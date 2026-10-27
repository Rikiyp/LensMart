<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Admin bisa melihat semua payment. Customer hanya miliknya sendiri.
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isCustomer() && $payment->customer_id === $user->id;
    }

    /**
     * Hanya customer pemilik payment yang belum lunas yang boleh memilih metode.
     */
    public function setMethod(User $user, Payment $payment): bool
    {
        return $user->isCustomer()
            && $payment->customer_id === $user->id
            && $payment->isUnpaid();
    }

    /**
     * Hanya customer pemilik payment yang boleh mensimulasikan pembayaran.
     */
    public function pay(User $user, Payment $payment): bool
    {
        return $user->isCustomer()
            && $payment->customer_id === $user->id
            && $payment->isUnpaid();
    }

    /**
     * Customer pemilik atau admin bisa cek status.
     */
    public function checkStatus(User $user, Payment $payment): bool
    {
        return $user->isAdmin()
            || ($user->isCustomer() && $payment->customer_id === $user->id);
    }
}
