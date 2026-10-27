<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Payment;

interface PaymentGateway
{
    public function name(): string;

    public function createPayment(Payment $payment, string $method): void;

    public function checkStatus(Payment $payment): PaymentStatus;
}
