<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi LensMart</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1e293b; font-size: 11px; margin: 0; padding: 24px; }
        h1 { font-size: 20px; margin: 0 0 4px; color: #0f172a; }
        .subtitle { color: #64748b; margin: 0 0 18px; }
        .brand { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #fbbf24; padding-bottom: 12px; margin-bottom: 18px; }
        .brand strong { font-size: 18px; color: #0f172a; }
        .brand span { color: #64748b; font-size: 10px; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .summary td { width: 25%; padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; }
        .summary .label { display: block; color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
        .summary .value { font-size: 14px; font-weight: bold; color: #0f172a; }
        .summary .green { color: #047857; }
        .summary .amber { color: #b45309; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #0f172a; color: #fff; text-align: left; padding: 7px 6px; font-size: 9px; text-transform: uppercase; letter-spacing: .04em; }
        table.data td { border-bottom: 1px solid #e2e8f0; padding: 7px 6px; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        .text-right { text-align: right; }
        .muted { color: #64748b; }
        .footer { margin-top: 18px; border-top: 1px solid #e2e8f0; padding-top: 8px; color: #94a3b8; font-size: 9px; }
        .empty { padding: 24px; text-align: center; color: #94a3b8; border: 1px dashed #cbd5e1; }
    </style>
</head>
<body>
    <div class="brand">
        <div>
            <strong>LensMart</strong><br>
            <span>Marketplace kamera, lensa, dan aksesori fotografi</span>
        </div>
        <div style="text-align:right">
            <strong style="font-size:13px">Laporan Transaksi</strong><br>
            <span>{{ format_tanggal($from) }} – {{ format_tanggal($to) }}</span>
        </div>
    </div>

    <h1>Ringkasan Penjualan</h1>
    <p class="subtitle">Dicetak pada {{ format_tanggal(now(), true) }} · {{ $summary['count'] }} pesanan selesai</p>

    <table class="summary">
        <tr>
            <td><span class="label">Total penjualan</span><span class="value">{{ format_rupiah($summary['gross']) }}</span></td>
            <td><span class="label">Subtotal produk</span><span class="value">{{ format_rupiah($summary['subtotal']) }}</span></td>
            <td><span class="label">Komisi platform</span><span class="value green">{{ format_rupiah($summary['commission']) }}</span></td>
            <td><span class="label">Pendapatan partner</span><span class="value amber">{{ format_rupiah($summary['subtotal'] - $summary['commission']) }}</span></td>
        </tr>
    </table>

    <h1 style="font-size:14px">Rincian Transaksi</h1>
    @if ($summary['orders']->isEmpty())
        <div class="empty">Tidak ada transaksi selesai pada periode ini.</div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Tanggal Selesai</th>
                    <th>Toko</th>
                    <th>Pelanggan</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Ongkir</th>
                    <th class="text-right">Komisi</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summary['orders'] as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ format_tanggal($order->completed_at, true) }}</td>
                        <td>{{ $order->store?->name }}</td>
                        <td>{{ $order->customer?->name }}</td>
                        <td class="text-right">{{ format_rupiah($order->subtotal) }}</td>
                        <td class="text-right">{{ format_rupiah($order->shipping_cost) }}</td>
                        <td class="text-right">{{ format_rupiah($order->commission_amount) }}</td>
                        <td class="text-right"><strong>{{ format_rupiah($order->total) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">Laporan ini dihasilkan otomatis oleh sistem LensMart. Data hanya mencakup pesanan berstatus selesai.</div>
</body>
</html>
