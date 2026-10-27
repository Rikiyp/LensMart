<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Address;
use App\Models\CartItem;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $selectedIds = collect($request->session()->get('checkout.cart_item_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $query = CartItem::query()
            ->where('user_id', $request->user()->id)
            ->with(['product.primaryImage', 'product.store', 'product.brand']);

        if ($selectedIds !== []) {
            $query->whereIn('id', $selectedIds);
        }

        $items = $query->get()
            ->filter(fn (CartItem $item) => $item->product !== null)
            ->values();

        if ($items->isEmpty()) {
            if ($selectedIds !== []) {
                $request->session()->forget('checkout.cart_item_ids');

                return redirect()
                    ->route('customer.cart.index')
                    ->with('error', 'Produk yang dipilih sudah tidak tersedia di keranjang.');
            }

            return redirect()->route('customer.cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $addresses = $request->user()->addresses()->orderByDesc('is_default')->latest()->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        $groups = $items->groupBy(fn (CartItem $item) => $item->product->store_id)
            ->map(function ($group) {
                return [
                    'store' => $group->first()->product->store,
                    'items' => $group,
                    'subtotal' => $group->sum(fn (CartItem $item) => $item->product->price * $item->quantity),
                ];
            });

        return view('customer.checkout', [
            'groups' => $groups,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'shippingMethods' => config('lensmart.shipping_methods'),
            'subtotal' => $items->sum(fn (CartItem $item) => $item->product->price * $item->quantity),
            'itemCount' => $items->sum('quantity'),
        ]);
    }

    public function store(CheckoutRequest $request, CheckoutService $checkoutService): RedirectResponse
    {
        $address = Address::findOrFail($request->integer('address_id'));

        $selectedIds = collect($request->session()->get('checkout.cart_item_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $payment = $checkoutService->checkout(
            $request->user(),
            $address,
            $request->string('shipping_method')->toString(),
            $request->input('note'),
            $selectedIds
        );

        $request->session()->forget('checkout.cart_item_ids');

        return redirect()
            ->route('customer.payments.show', $payment)
            ->with('success', 'Pesanan berhasil dibuat. Selesaikan pembayaran sebelum 24 jam.');
    }
}
