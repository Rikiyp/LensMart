<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * Membuat satu pembayaran dan satu pesanan per toko dari isi keranjang.
     *
     * @param  array<int, int>  $cartItemIds  Batasi checkout pada item keranjang tertentu.
     */
    public function checkout(User $customer, Address $address, string $shippingMethod, ?string $note = null, array $cartItemIds = []): Payment
    {
        $shipping = config('lensmart.shipping_methods.'.$shippingMethod);

        if (! $shipping) {
            throw ValidationException::withMessages(['shipping_method' => 'Metode pengiriman tidak valid.']);
        }

        if ($address->user_id !== $customer->id) {
            throw ValidationException::withMessages(['address_id' => 'Alamat tidak ditemukan.']);
        }

        return DB::transaction(function () use ($customer, $address, $shippingMethod, $shipping, $note, $cartItemIds) {
            $cartItems = CartItem::query()
                ->where('user_id', $customer->id)
                ->when($cartItemIds !== [], fn ($query) => $query->whereIn('id', $cartItemIds))
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
            }

            $products = Product::query()
                ->with(['store.user', 'primaryImage'])
                ->whereIn('id', $cartItems->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            /** @var Collection<int, Collection<int, CartItem>> $groups */
            $groups = collect();

            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'cart' => 'Salah satu produk di keranjang sudah dihapus. Hapus dari keranjang untuk melanjutkan.',
                    ]);
                }

                if (! $product->isPubliclyVisible()) {
                    throw ValidationException::withMessages([
                        'cart' => "Produk \"{$product->name}\" sudah tidak tersedia. Hapus dari keranjang untuk melanjutkan.",
                    ]);
                }

                if ($item->quantity < 1 || $product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok \"{$product->name}\" tidak mencukupi (tersisa {$product->stock}).",
                    ]);
                }

                $groups->put(
                    $product->store_id,
                    $groups->get($product->store_id, collect())->push($item)
                );
            }

            $orderPayloads = [];
            $paymentAmount = 0;

            foreach ($groups as $storeId => $items) {
                $subtotal = $items->sum(fn (CartItem $item) => $products->get($item->product_id)->price * $item->quantity);
                $commission = (int) round($subtotal * (float) config('lensmart.commission_rate'));
                $shippingCost = (int) $shipping['cost'];
                $total = $subtotal + $shippingCost;

                $orderPayloads[] = [
                    'store_id' => $storeId,
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'commission' => $commission,
                    'shipping_cost' => $shippingCost,
                    'total' => $total,
                ];

                $paymentAmount += $total;
            }

            $payment = Payment::create([
                'code' => Payment::generateCode(),
                'customer_id' => $customer->id,
                'amount' => $paymentAmount,
                'method' => null,
                'status' => PaymentStatus::Unpaid,
                'gateway' => config('lensmart.payment.driver', 'simulated'),
                'expires_at' => now()->addHours((int) config('lensmart.payment.expiry_hours', 24)),
            ]);

            foreach ($orderPayloads as $payload) {
                $order = Order::create([
                    'order_number' => Order::generateNumber(),
                    'payment_id' => $payment->id,
                    'customer_id' => $customer->id,
                    'store_id' => $payload['store_id'],
                    'status' => OrderStatus::WaitingConfirmation,
                    'recipient_name' => $address->recipient_name,
                    'recipient_phone' => $address->phone,
                    'shipping_address' => $address->full_address,
                    'shipping_method' => $shippingMethod,
                    'shipping_cost' => $payload['shipping_cost'],
                    'subtotal' => $payload['subtotal'],
                    'commission_amount' => $payload['commission'],
                    'total' => $payload['total'],
                    'note' => $note,
                ]);

                foreach ($payload['items'] as $item) {
                    $product = $products->get($item->product_id);

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->price,
                        'quantity' => $item->quantity,
                        'subtotal' => $product->price * $item->quantity,
                    ]);

                    $product->stock -= $item->quantity;
                    // Status (active/out_of_stock) diurus otomatis oleh ProductObserver::saving().
                    $product->save();
                }

                $order->histories()->create([
                    'status' => OrderStatus::WaitingConfirmation,
                    'note' => 'Pesanan dibuat, menunggu konfirmasi toko.',
                    'changed_by' => $customer->id,
                ]);
            }

            CartItem::where('user_id', $customer->id)
                ->whereIn('id', $cartItems->pluck('id')->all())
                ->delete();

            return $payment->load(['orders.items', 'orders.store']);
        });
    }
}
