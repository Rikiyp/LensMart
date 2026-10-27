<x-layouts.portal title="Dashboard Admin" subtitle="Ringkasan aktivitas marketplace dan antrean moderasi.">
    @php
        $maxRevenue = max(array_merge($chart['revenue'], [1]));
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['users', 'Pelanggan', number_format($stats['customers'], 0, ',', '.'), 'akun terdaftar'],
            ['store', 'Partner toko', number_format($stats['partners'], 0, ',', '.'), 'toko terdaftar'],
            ['cube', 'Produk', number_format($stats['products'], 0, ',', '.'), $stats['pending_products'].' menunggu verifikasi'],
            ['shopping-bag', 'Pesanan', number_format($stats['orders'], 0, ',', '.'), $stats['unpaid_payments'].' pembayaran belum lunas'],
        ] as $card)
            @php
                [$icon, $label, $value, $hint] = $card;
            @endphp
            <div class="stat-card">
                <div class="flex items-center justify-between gap-3">
                    <p class="field-label">{{ $label }}</p>
                    <x-icon :name="$icon" class="h-4 w-4 text-ink-400" />
                </div>
                <p class="tabular mt-3 font-sans text-[25px] font-semibold leading-none text-ink-950">{{ $value }}</p>
                <p class="text-[10.5px] uppercase tracking-[0.1em] text-ink-400">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card border-l-2 border-l-ink-950 p-5">
            <p class="field-label">Pendapatan selesai</p>
            <p class="tabular mt-2 font-sans text-[22px] font-semibold leading-none text-ink-950">{{ format_rupiah($stats['revenue']) }}</p>
            <p class="mt-2 text-[10.5px] text-ink-500">Total nilai pesanan berstatus selesai</p>
        </div>
        <div class="card border-l-2 border-l-emerald-600 p-5">
            <p class="field-label">Komisi platform</p>
            <p class="tabular mt-2 font-sans text-[22px] font-semibold leading-none text-emerald-700">{{ format_rupiah($stats['commission']) }}</p>
            <p class="mt-2 text-[10.5px] text-ink-500">Pendapatan komisi LensMart</p>
        </div>
        <div class="card border-l-2 border-l-amber-500 p-5">
            <p class="field-label">Antrean verifikasi toko</p>
            <p class="tabular mt-2 font-sans text-[22px] font-semibold leading-none text-ink-950">{{ $stats['pending_stores'] }}</p>
            <a href="{{ route('admin.partners.index', ['status' => 'pending']) }}" class="mt-2 inline-flex items-center gap-1 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-ink-600 transition hover:text-ink-950">
                Tinjau sekarang <x-icon name="arrow-right" class="h-3 w-3" />
            </a>
        </div>
        <div class="card border-l-2 border-l-rose-600 p-5">
            <p class="field-label">Laporan ulasan terbuka</p>
            <p class="tabular mt-2 font-sans text-[22px] font-semibold leading-none text-ink-950">{{ $stats['open_reports'] }}</p>
            <a href="{{ route('admin.reviews.index') }}" class="mt-2 inline-flex items-center gap-1 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-ink-600 transition hover:text-ink-950">
                Lihat laporan <x-icon name="arrow-right" class="h-3 w-3" />
            </a>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-[1.4fr_1fr]">
        <section class="card p-5">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-[13px] font-semibold text-ink-950">Pendapatan 6 bulan terakhir</h2>
                    <p class="mt-0.5 text-[11px] text-ink-500">Subtotal pesanan selesai dan komisi platform.</p>
                </div>
                <div class="flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.1em] text-ink-500">
                    <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-amber-400"></span> Pendapatan</span>
                    <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-ink-950"></span> Komisi</span>
                </div>
            </div>
            <div class="mt-6 flex h-48 items-end gap-3">
                @foreach ($chart['labels'] as $index => $label)
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                        <div class="flex w-full max-w-12 items-end justify-center gap-1" style="height: 100%">
                            <div class="w-1/2 rounded-t-sm bg-amber-400" style="height: {{ max(3, ($chart['revenue'][$index] / $maxRevenue) * 100) }}%" title="{{ format_rupiah($chart['revenue'][$index]) }}"></div>
                            <div class="w-1/3 rounded-t-sm bg-ink-950/80" style="height: {{ max(2, ($chart['commission'][$index] / $maxRevenue) * 100) }}%" title="{{ format_rupiah($chart['commission'][$index]) }}"></div>
                        </div>
                        <span class="whitespace-nowrap text-[9px] font-semibold uppercase tracking-[0.08em] text-ink-400">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-paper-200 px-5 py-4">
                <div>
                    <h2 class="text-[13px] font-semibold text-ink-950">Toko teratas</h2>
                    <p class="mt-0.5 text-[11px] text-ink-500">Berdasarkan penjualan selesai.</p>
                </div>
                <a href="{{ route('admin.partners.index') }}" class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-500 transition hover:text-ink-950">Semua</a>
            </div>
            @if ($topStores->isEmpty())
                <p class="px-5 py-8 text-center text-[11px] text-ink-400">Belum ada data penjualan.</p>
            @else
                <ul class="divide-y divide-paper-200">
                    @foreach ($topStores as $index => $store)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <span class="tabular flex h-6 w-6 shrink-0 items-center justify-center rounded border border-paper-300 bg-paper-50 text-[10.5px] font-bold text-ink-500">{{ $index + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.partners.show', $store) }}" class="block truncate text-[11.5px] font-semibold text-ink-950 transition hover:text-amber-700">{{ $store->name }}</a>
                                <p class="text-[10px] text-ink-400">{{ $store->completed_orders_count }} pesanan selesai</p>
                            </div>
                            <span class="tabular shrink-0 text-[11.5px] font-bold text-ink-950">{{ format_rupiah((int) $store->completed_sales) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section class="card mt-4 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-paper-200 px-5 py-4">
            <div>
                <h2 class="text-[13px] font-semibold text-ink-950">Pesanan terbaru</h2>
                <p class="mt-0.5 text-[11px] text-ink-500">Semua transaksi terbaru di marketplace.</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn-outline btn-sm">Lihat semua</a>
        </div>

        @if ($recentOrders->isEmpty())
            <div class="p-5">
                <x-empty-state icon="shopping-bag" title="Belum ada pesanan" description="Pesanan pelanggan akan muncul di sini." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Toko</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td class="whitespace-nowrap">
                                    <p class="tabular text-[12px] font-semibold text-ink-950">{{ $order->order_number }}</p>
                                    <p class="mt-0.5 text-[10px] text-ink-400">{{ format_tanggal($order->created_at, true) }}</p>
                                </td>
                                <td class="text-[11.5px] text-ink-600">{{ $order->customer?->name }}</td>
                                <td class="text-[11.5px] text-ink-600">{{ $order->store?->name }}</td>
                                <td class="tabular whitespace-nowrap text-[12px] font-bold text-ink-950">{{ format_rupiah($order->total) }}</td>
                                <td><span class="badge {{ $order->payment?->status->badge() ?? 'bg-paper-100 text-ink-600 ring-paper-400' }}">{{ $order->payment?->status->label() ?? '-' }}</span></td>
                                <td><span class="badge {{ $order->status->badge() }}">{{ $order->status->label() }}</span></td>
                                <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="btn-outline btn-sm">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.portal>
