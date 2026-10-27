<x-layouts.portal title="Keuangan" subtitle="Ringkasan penjualan, komisi, dan pencairan dana toko.">
    @php
        $maxRevenue = max(array_merge($chart['revenue'], [1]));
        $net = $summary['subtotal'] - $summary['commission'];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['receipt', 'Pesanan selesai', $summary['count'], 'periode terpilih', 'bg-sky-50 text-sky-600'],
            ['banknotes', 'Penjualan kotor', format_rupiah($summary['gross']), 'termasuk ongkir', 'bg-emerald-50 text-emerald-600'],
            ['wallet', 'Komisi platform', format_rupiah($summary['commission']), '5% dari subtotal', 'bg-rose-50 text-rose-600'],
            ['chart-bar', 'Pendapatan bersih', format_rupiah($net), 'sebelum pajak', 'bg-amber-50 text-amber-600'],
        ] as $card)
            @php
                [$icon, $label, $value, $hint, $color] = $card;
            @endphp
            <div class="card p-5">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $color }}">
                        <x-icon :name="$icon" class="h-5 w-5" />
                    </span>
                </div>
                <p class="mt-4 truncate text-2xl font-extrabold tracking-tight text-ink-950">{{ $value }}</p>
                <p class="mt-1 text-xs font-semibold text-ink-500">{{ $label }}</p>
                <p class="mt-0.5 text-[10px] text-ink-400">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('partner.finance.index') }}" class="card mt-5 flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="from" class="label text-xs">Dari tanggal</label>
            <input id="from" type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input !py-2 text-xs">
        </div>
        <div class="flex-1">
            <label for="to" class="label text-xs">Sampai tanggal</label>
            <input id="to" type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input !py-2 text-xs">
        </div>
        <button type="submit" class="btn-primary btn-sm">Terapkan periode</button>
        <a href="{{ route('partner.finance.index') }}" class="btn-ghost btn-sm justify-center">Bulan ini</a>
    </form>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.4fr_1fr]">
        <section class="card p-5">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-sm font-extrabold text-ink-950">Tren penjualan</h2>
                    <p class="text-xs text-ink-500">Enam bulan terakhir berdasarkan pesanan selesai.</p>
                </div>
                <div class="flex items-center gap-3 text-[10px] font-semibold text-ink-500">
                    <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Penjualan</span>
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

        <section class="card p-5">
            <h2 class="text-sm font-extrabold text-ink-950">Rekening pencairan</h2>
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-ink-500">Bank</span><span class="font-semibold text-ink-950">{{ $store->bank_name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-ink-500">Nomor rekening</span><span class="font-mono font-semibold text-ink-950">{{ $store->account_number ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-ink-500">Pemilik</span><span class="font-semibold text-ink-950">{{ $store->account_holder ?? '-' }}</span></div>
            </div>
            <a href="{{ route('partner.store.edit') }}" class="btn-outline mt-4 w-full btn-sm">Perbarui rekening</a>
            <div class="mt-4 rounded-xl bg-sky-50 px-3 py-2.5 text-[11px] leading-relaxed text-sky-900 ring-1 ring-inset ring-sky-600/20">
                Pencairan diproses admin setelah pesanan selesai. Anda dapat memantau statusnya pada tabel di bawah.
            </div>
        </section>
    </div>

    <section class="card mt-5 overflow-hidden">
        <div class="flex items-center justify-between border-b border-paper-200 px-5 py-4">
            <div>
                <h2 class="text-sm font-extrabold text-ink-950">Riwayat pencairan</h2>
                <p class="text-xs text-ink-500">Pencairan dibuat otomatis saat pesanan selesai.</p>
            </div>
            <span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $payouts->total() }} pencairan</span>
        </div>

        @if ($payouts->isEmpty())
            <div class="p-5">
                <x-empty-state icon="wallet" title="Belum ada pencairan" description="Riwayat pencairan akan muncul setelah pesanan selesai dan diterima pelanggan." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pesanan</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Dibayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payouts as $payout)
                            <tr>
                                <td class="whitespace-nowrap text-xs text-ink-500">{{ format_tanggal($payout->created_at, true) }}</td>
                                <td>
                                    @if ($payout->order)
                                        <a href="{{ route('partner.orders.show', $payout->order) }}" class="text-xs font-bold text-ink-950 hover:text-amber-600">{{ $payout->order->order_number }}</a>
                                    @else
                                        <span class="text-xs text-ink-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-sm font-extrabold text-ink-950">{{ format_rupiah($payout->amount) }}</td>
                                <td><span class="badge {{ $payout->status->badge() }}">{{ $payout->status->label() }}</span></td>
                                <td class="whitespace-nowrap text-xs text-ink-500">{{ $payout->paid_at ? format_tanggal($payout->paid_at, true) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="mt-5">{{ $payouts->links() }}</div>
</x-layouts.portal>
