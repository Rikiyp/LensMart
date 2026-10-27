<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Payment;

class SimulatedGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'simulated';
    }

    public function createPayment(Payment $payment, string $method): void
    {
        $payment->method = $method;
        $payment->gateway = $this->name();
        $payment->gateway_reference = match ($method) {
            'transfer_bank' => '8808'.str_pad((string) $payment->id, 10, '0', STR_PAD_LEFT),
            'qris' => 'QR-'.strtoupper(bin2hex(random_bytes(5))),
            'ewallet' => 'EW-'.strtoupper(bin2hex(random_bytes(5))),
            default => 'SIM-'.strtoupper(bin2hex(random_bytes(5))),
        };
        $payment->save();
    }

    public function checkStatus(Payment $payment): PaymentStatus
    {
        return $payment->status;
    }
}
