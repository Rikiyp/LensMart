<?php

namespace App\Http\Controllers\Customer;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function show(Request $request, Payment $payment): View
    {
        abort_unless($payment->customer_id === $request->user()->id, 403);

        $this->paymentService->expireDueFor($request->user());
        $payment->refresh()->load(['orders.store', 'orders.items']);

        return view('customer.payments.show', [
            'payment' => $payment,
            'methods' => config('lensmart.payment_methods'),
        ]);
    }

    public function setMethod(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->customer_id === $request->user()->id, 403);

        $validated = $request->validate([
            'method' => ['required', 'string', Rule::in(array_keys(config('lensmart.payment_methods', [])))],
        ], [
            'method.in' => 'Metode pembayaran tidak valid.',
        ]);

        $this->paymentService->setMethod($payment, $validated['method']);

        return back()->with('success', 'Metode pembayaran dipilih. Silakan selesaikan pembayaran.');
    }

    public function pay(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->customer_id === $request->user()->id, 403);

        if ($payment->isUnpaid() && ! $payment->method) {
            return back()->with('error', 'Pilih metode pembayaran terlebih dahulu.');
        }

        $this->paymentService->markPaid($payment);

        return back()->with('success', 'Pembayaran berhasil disimulasikan. Pesanan diteruskan ke toko.');
    }

    public function status(Request $request, Payment $payment): JsonResponse
    {
        abort_unless($payment->customer_id === $request->user()->id, 403);

        $status = $this->paymentService->checkStatus($payment);
        $payment->refresh();

        return response()->json([
            'status' => $status->value,
            'label' => $status->label(),
            'paid' => $status === PaymentStatus::Paid,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ]);
    }
}
