<x-layouts.portal title="Laporan Transaksi" subtitle="Rekap pesanan selesai, komisi platform, dan ekspor data.">
    @php
        $maxRevenue = max(array_merge($chart['revenue'], [1]));
        $netPartner = $summary['subtotal'] - $summary['commission'];
    @endphp

    <form method="GET" action="{{ route('admin.reports.index') }}" class="card mb-5 flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="from" class="label text-xs">Dari tanggal</label>
            <input id="from" type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input !py-2 text-xs">
        </div>
        <div class="flex-1">
            <label for="to" class="label text-xs">Sampai tanggal</label>
            <input id="to" type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input !py-2 text-xs">
        </div>
        <button type="submit" class="btn-primary btn-sm">Terapkan periode</button>
        <a href="{{ route('admin.reports.index') }}" class="btn-ghost btn-sm justify-center">Bulan ini</a>
    </form>

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs text-ink-500">Periode {{ format_tanggal($from) }} – {{ format_tanggal($to) }}</p>
            <h2 class="mt-1 text-lg font-extrabold text-ink-950">Ringkasan {{ $summary['count'] }} pesanan selesai</h2>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.pdf', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn-outline btn-sm">
                <x-icon name="document" class="h-3.5 w-3.5" /> Unduh PDF
            </a>
            <a href="{{ route('admin.reports.excel', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn-primary btn-sm">
                <x-icon name="arrow-down-tray" class="h-3.5 w-3.5" /> Ekspor Excel
            </a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <span class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Total penjualan</span>
            <p class="mt-1 text-2xl font-extrabold text-ink-950">{{ format_rupiah($summary['gross']) }}</p>
            <p class="text-[10px] text-ink-400">Termasuk ongkir</p>
        </div>
        <div class="stat-card">
            <span class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Subtotal produk</span>
            <p class="mt-1 text-2xl font-extrabold text-ink-950">{{ format_rupiah($summary['subtotal']) }}</p>
            <p class="text-[10px] text-ink-400">Sebelum ongkir</p>
        </div>
        <div class="stat-card">
            <span class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Komisi platform</span>
            <p class="mt-1 text-2xl font-extrabold text-emerald-600">{{ format_rupiah($summary['commission']) }}</p>
            <p class="text-[10px] text-ink-400">Pendapatan LensMart</p>
        </div>
        <div class="stat-card">
            <span class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Pendapatan partner</span>
            <p class="mt-1 text-2xl font-extrabold text-amber-600">{{ format_rupiah($netPartner) }}</p>
            <p class="text-[10px] text-ink-400">Subtotal dikurangi komisi</p>
        </div>
    </div>

    <section class="card mt-5 p-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-sm font-extrabold text-ink-950">Tren 6 bulan</h2>
                <p class="text-xs text-ink-500">Pendapatan dan komisi dari pesanan selesai.</p>
            </div>
            <div class="flex items-center gap-3 text-[10px] font-semibold text-ink-500">
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Pendapatan</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-ink-950"></span> Komisi</span>
            </div>
        </div>
        <div class="mt-6 flex h-52 items-end gap-3">
            @foreach ($chart['labels'] as $index => $label)
                <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                    <div class="flex w-full max-w-12 items-end justify-center gap-1" style="height: 100%">
                        <div class="w-1/2 rounded-t-md bg-amber-400" style="height: {{ max(3, ($chart['revenue'][$index] / $maxRevenue) * 100) }}%" title="{{ format_rupiah($chart['revenue'][$index]) }}"></div>
                        <div class="w-1/3 rounded-t-md bg-ink-950/80" style="height: {{ max(2, ($chart['commission'][$index] / $maxRevenue) * 100) }}%" title="{{ format_rupiah($chart['commission'][$index]) }}"></div>
                    </div>
                    <span class="whitespace-nowrap text-[9px] font-semibold text-ink-400">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="card mt-5 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-paper-200 px-5 py-4">
            <div>
                <h2 class="text-sm font-extrabold text-ink-950">Rincian transaksi</h2>
                <p class="text-xs text-ink-500">Pesanan berstatus selesai pada periode terpilih.</p>
            </div>
            <span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $summary['orders']->count() }} transaksi</span>
        </div>

        @if ($summary['orders']->isEmpty())
            <div class="p-5">
                <x-empty-state icon="chart-bar" title="Tidak ada transaksi selesai" description="Pilih periode lain atau tunggu pesanan diselesaikan pelanggan." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Selesai</th>
                            <th>Toko</th>
                            <th>Pelanggan</th>
                            <th>Subtotal</th>
                            <th>Ongkir</th>
                            <th>Komisi</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summary['orders'] as $order)
                            <tr>
                                <td class="whitespace-nowrap">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold text-ink-950 hover:text-amber-600">{{ $order->order_number }}</a>
                                </td>
                                <td class="whitespace-nowrap text-xs text-ink-500">{{ format_tanggal($order->completed_at, true) }}</td>
                                <td class="text-xs text-ink-600">{{ $order->store?->name }}</td>
                                <td class="text-xs text-ink-600">{{ $order->customer?->name }}</td>
                                <td class="whitespace-nowrap text-xs font-semibold">{{ format_rupiah($order->subtotal) }}</td>
                                <td class="whitespace-nowrap text-xs text-ink-500">{{ format_rupiah($order->shipping_cost) }}</td>
                                <td class="whitespace-nowrap text-xs font-semibold text-emerald-600">{{ format_rupiah($order->commission_amount) }}</td>
                                <td class="whitespace-nowrap text-sm font-extrabold text-ink-950">{{ format_rupiah($order->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.portal>
