<?php

namespace App\Console\Commands;

use App\Services\Payment\PaymentService;
use Illuminate\Console\Command;

class ExpirePayments extends Command
{
    protected $signature = 'payments:expire';

    protected $description = 'Batalkan pesanan dan kembalikan stok untuk pembayaran yang kedaluwarsa';

    public function handle(PaymentService $paymentService): int
    {
        $count = $paymentService->expireDueFor();

        $this->info("{$count} pembayaran kedaluwarsa diproses.");

        return self::SUCCESS;
    }
}
