<?php

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

if (! function_exists('format_rupiah')) {
    function format_rupiah(int|float|null $amount, bool $withPrefix = true): string
    {
        $formatted = number_format((float) ($amount ?? 0), 0, ',', '.');

        return $withPrefix ? 'Rp '.$formatted : $formatted;
    }
}

if (! function_exists('format_tanggal')) {
    function format_tanggal(CarbonInterface|string|null $date, bool $withTime = false): string
    {
        if (! $date) {
            return '-';
        }

        $date = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        return $date->translatedFormat($withTime ? 'd M Y, H:i' : 'd M Y');
    }
}

if (! function_exists('shipping_method')) {
    /**
     * @return array{label: string, cost: int, eta: string}|null
     */
    function shipping_method(?string $key): ?array
    {
        return config('lensmart.shipping_methods.'.$key);
    }
}

if (! function_exists('shipping_cost')) {
    function shipping_cost(?string $key): int
    {
        return (int) (shipping_method($key)['cost'] ?? 0);
    }
}

if (! function_exists('payment_method_label')) {
    function payment_method_label(?string $key): string
    {
        if (! $key) {
            return '-';
        }

        return config('lensmart.payment_methods.'.$key.'.label', ucfirst($key));
    }
}

if (! function_exists('lensmart_product_image')) {
    function lensmart_product_image(?string $path): string
    {
        return $path
            ? asset('storage/'.$path)
            : asset('images/placeholder-product.svg');
    }
}
