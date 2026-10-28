<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\ProductStatus;
use App\Events\OrderStatusUpdated as OrderStatusUpdatedEvent;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    /**
     * Partner memajukan status satu langkah (menunggu → dikonfirmasi → dikemas → dikirim).
     */
    public function advance(Order $order, User $partner, ?string $trackingNumber = null, ?string $note = null): Order
    {
        abort_unless(
            $partner->isPartner() && $partner->store && $order->store_id === $partner->store->id,
            403
        );

        return DB::transaction(function () use ($order, $partner, $trackingNumber, $note) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $order->isPaid()) {
                throw ValidationException::withMessages(['status' => 'Pesanan belum dibayar.']);
            }

            $oldStatus = $order->status->value;
            $next = $order->status->next();

            if (! $next || $next === OrderStatus::Completed) {
                throw ValidationException::withMessages(['status' => 'Status pesanan tidak dapat diubah oleh partner.']);
            }

            if ($next === OrderStatus::Shipped && blank($trackingNumber)) {
                throw ValidationException::withMessages(['tracking_number' => 'Nomor resi wajib diisi saat mengirim pesanan.']);
            }

            $order->status = $next;

            if ($next === OrderStatus::Shipped) {
                $order->tracking_number = $trackingNumber;
            }

            $order->save();

            $order->histories()->create([
                'status' => $next,
                'note' => $note,
                'changed_by' => $partner->id,
            ]);

            $order->customer->notify(new OrderStatusUpdated($order, $note));

            // Broadcast real-time event
            OrderStatusUpdatedEvent::dispatch($order, $oldStatus, $next->value);

            // Database notification for customer
            $order->customer->notifications()->create([
                'type' => 'order_status_updated',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'old_status' => $oldStatus,
                    'new_status' => $next->value,
                    'status_label' => $next->label(),
                ],
            ]);

            return $order;
        });
    }

    /**
     * Pelanggan menekan "Pesanan Diterima" pada status dikirim.
     */
    public function confirmReceived(Order $order, User $customer): Order
    {
        abort_unless($customer->isCustomer() && $order->customer_id === $customer->id, 403);

        return DB::transaction(function () use ($order, $customer) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== OrderStatus::Shipped) {
                throw ValidationException::withMessages(['status' => 'Pesanan belum dikirim.']);
            }

            $oldStatus = $order->status->value;

            $order->update([
                'status' => OrderStatus::Completed,
                'completed_at' => now(),
            ]);

            $order->histories()->create([
                'status' => OrderStatus::Completed,
                'note' => 'Pesanan diterima oleh pelanggan.',
                'changed_by' => $customer->id,
            ]);

            $this->createPayout($order);

            if ($order->store?->user) {
                $order->store->user->notify(new OrderStatusUpdated($order, 'Pesanan telah diterima pelanggan.'));
                
                // Database notification for partner
                $order->store->user->notifications()->create([
                    'type' => 'order_completed',
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'message' => "Pesanan {$order->order_number} telah diterima pelanggan",
                    ],
                ]);
            }

            // Broadcast real-time event
            OrderStatusUpdatedEvent::dispatch($order, $oldStatus, OrderStatus::Completed->value);

            return $order;
        });
    }

    public function cancel(Order $order, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $note) {
            // Ambil state terkini setelah lock untuk menghindari race condition.
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            // Otorisasi dan cek status dilakukan setelah lock agar atomik.
            abort_unless($order->canBeCancelledBy($actor), 403);

            if (! $order->status->isCancellable()) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan hanya dapat dibatalkan saat menunggu konfirmasi.',
                ]);
            }

            $oldStatus = $order->status->value;

            $this->restoreStock($order);

            $order->update(['status' => OrderStatus::Cancelled]);

            $order->histories()->create([
                'status'     => OrderStatus::Cancelled,
                'note'       => $note ?: 'Pesanan dibatalkan.',
                'changed_by' => $actor->id,
            ]);

            $order->customer->notify(new OrderStatusUpdated($order, $note));

            if ($order->store?->user && $order->store->user->id !== $actor->id) {
                $order->store->user->notify(new OrderStatusUpdated($order, $note));
            }

            // Broadcast real-time event
            OrderStatusUpdatedEvent::dispatch($order, $oldStatus, OrderStatus::Cancelled->value);

            // Database notification for customer
            $order->customer->notifications()->create([
                'type' => 'order_cancelled',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'message' => "Pesanan {$order->order_number} dibatalkan",
                    'note' => $note,
                ],
            ]);

            // Database notification for partner
            if ($order->store?->user && $order->store->user->id !== $actor->id) {
                $order->store->user->notifications()->create([
                    'type' => 'order_cancelled',
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'message' => "Pesanan {$order->order_number} dibatalkan oleh " . ($actor->isCustomer() ? 'pelanggan' : 'partner'),
                        'note' => $note,
                    ],
                ]);
            }

            return $order;
        });
    }

    public function restoreStock(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = Product::withTrashed()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->first();

            if (! $product || $product->trashed()) {
                continue;
            }

            $product->stock += $item->quantity;

            if ($product->status === ProductStatus::OutOfStock && $product->stock > 0) {
                $product->status = ProductStatus::Active;
            }

            $product->save();
        }
    }

    public function createPayout(Order $order): Payout
    {
        return Payout::firstOrCreate(
            ['order_id' => $order->id],
            [
                'store_id' => $order->store_id,
                'amount' => max(0, $order->subtotal - $order->commission_amount),
                'status' => PayoutStatus::Pending,
            ]
        );
    }
}
