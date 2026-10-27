<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Collection $orders,
        private readonly Carbon $from,
        private readonly Carbon $to,
    ) {
    }

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'No. Pesanan',
            'Tanggal Selesai',
            'Toko',
            'Pelanggan',
            'Subtotal',
            'Ongkir',
            'Komisi Platform',
            'Total',
            'Metode Pembayaran',
            'Status Pembayaran',
        ];
    }

    /**
     * @param  Order  $order
     */
    public function map($order): array
    {
        return [
            $order->order_number,
            $order->completed_at?->format('d/m/Y H:i'),
            $order->store?->name,
            $order->customer?->name,
            $order->subtotal,
            $order->shipping_cost,
            $order->commission_amount,
            $order->total,
            $order->payment?->method_label,
            $order->payment?->status->label(),
        ];
    }
}
