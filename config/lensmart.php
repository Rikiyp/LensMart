<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Komisi Platform
    |--------------------------------------------------------------------------
    | Persentase komisi diambil dari subtotal produk (bukan ongkir).
    */
    'commission_rate' => 0.05,

    /*
    |--------------------------------------------------------------------------
    | Pembayaran
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'driver' => env('PAYMENT_DRIVER', 'simulated'),
        'expiry_hours' => 24,
    ],

    'payment_methods' => [
        'transfer_bank' => [
            'label' => 'Transfer Bank',
            'description' => 'Transfer ke virtual account yang tertera.',
            'icon' => 'banknotes',
        ],
        'ewallet' => [
            'label' => 'E-Wallet',
            'description' => 'Bayar dengan saldo dompet digital Anda.',
            'icon' => 'device-phone-mobile',
        ],
        'qris' => [
            'label' => 'QRIS',
            'description' => 'Pindai kode QR dengan aplikasi pembayaran apa pun.',
            'icon' => 'qr-code',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Metode Pengiriman (statis, tanpa integrasi API)
    |--------------------------------------------------------------------------
    */
    'shipping_methods' => [
        'jne_reguler' => ['label' => 'JNE Reguler', 'cost' => 20000, 'eta' => '2-3 hari'],
        'jnt' => ['label' => 'J&T Express', 'cost' => 18000, 'eta' => '2-4 hari'],
        'sicepat' => ['label' => 'SiCepat Halu', 'cost' => 17000, 'eta' => '2-4 hari'],
        'instant' => ['label' => 'Instant (Same Day)', 'cost' => 40000, 'eta' => 'Hari ini'],
    ],

    'store' => [
        'name' => 'LensMart',
        'tagline' => 'Temukan kamera impianmu.',
        'email' => 'halo@lensmart.test',
        'phone' => '021-555-0199',
        'address' => 'Jl. Fotografi No. 12, Jakarta Selatan',
    ],

];
