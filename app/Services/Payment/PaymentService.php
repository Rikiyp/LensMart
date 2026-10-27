<?php

namespace App\Services\Payment;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\OrderStatusUpdated;
use App\Notifications\PaymentPaid;
use App\Services\OrderStatusService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private readonly OrderStatusService $orderStatusService)
    {
    }

    public function gateway(): PaymentGateway
    {
        return match (config('lensmart.payment.driver', 'simulated')) {
            default => new SimulatedGateway,
        };
    }

    public function setMethod(Payment $payment, string $method): Payment
    {
        if (! array_key_exists($method, config('lensmart.payment_methods'))) {
            throw ValidationException::withMessages(['method' => 'Metode pembayaran tidak valid.']);
        }

        if (! $payment->isUnpaid()) {
            return $payment;
        }

        $this->gateway()->createPayment($payment, $method);

        return $payment->refresh();
    }

    public function markPaid(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->isPaid()) {
                return $payment;
            }

            if ($payment->isExpired()) {
                $this->expire($payment);

                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran sudah kedaluwarsa. Silakan buat pesanan baru.',
                ]);
            }

            $payment->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ]);

            $payment->customer->notify(new PaymentPaid($payment));

            $payment->load('orders.store.user');

            foreach ($payment->orders as $order) {
                if ($order->store?->user) {
                    $order->store->user->notify(new PaymentPaid($payment, $order));
                }
            }

            return $payment;
        });
    }

    public function expire(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if (! $payment->isUnpaid()) {
                return;
            }

            $payment->load('orders.items.product');

            foreach ($payment->orders as $order) {
                if ($order->status === OrderStatus::Cancelled) {
                    continue;
                }

                try {
                    $this->orderStatusService->restoreStock($order);
                    $order->update(['status' => OrderStatus::Cancelled]);
                    $order->histories()->create([
                        'status'     => OrderStatus::Cancelled,
                        'note'       => 'Pembayaran kedaluwarsa. Stok dikembalikan otomatis.',
                        'changed_by' => null,
                    ]);

                    if ($order->customer) {
                        $order->customer->notify(new OrderStatusUpdated($order, 'Pembayaran kedaluwarsa.'));
                    }
                } catch (\Throwable $e) {
                    // Log error per-order tanpa menghentikan pemrosesan order lain.
                    logger()->error('Gagal memproses expire untuk order '.$order->id.': '.$e->getMessage());
                }
            }

            $payment->update(['status' => PaymentStatus::Expired]);
        });
    }

    public function expireDueFor(?User $customer = null): int
    {
        $query = Payment::query()
            ->where('status', PaymentStatus::Unpaid->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());

        if ($customer) {
            $query->where('customer_id', $customer->id);
        }

        $count = 0;

        foreach ($query->get() as $payment) {
            $this->expire($payment);
            $count++;
        }

        return $count;
    }

    /**
     * Cek status terkini; sekaligus menandai kedaluwarsa bila waktunya habis.
     */
    public function checkStatus(Payment $payment): PaymentStatus
    {
        if ($payment->isUnpaid() && $payment->isExpired()) {
            $this->expire($payment);
            $payment->refresh();
        }

        return $this->gateway()->checkStatus($payment);
    }
}
