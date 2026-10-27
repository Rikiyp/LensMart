<x-layouts.portal title="Dashboard Partner" :subtitle="$store->name">
    @php
        $maxRevenue = max(array_merge($chart['revenue'], [1]));
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['shopping-bag', 'Pesanan baru', $stats['new_orders'], 'menunggu konfirmasi'],
            ['cube', 'Produk aktif', $stats['active_products'].' / '.$stats['products'], 'dari total produk'],
            ['banknotes', 'Penjualan selesai', format_rupiah($stats['sales']), 'subtotal produk'],
            ['wallet', 'Pencairan tertunda', format_rupiah($stats['pending_payout']), 'komisi '.format_rupiah($stats['commission'])],
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

    {{-- Revenue chart --}}
    <section class="card mt-4 p-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-[13px] font-semibold text-ink-950">Penjualan 6 bulan terakhir</h2>
                <p class="mt-0.5 text-[11px] text-ink-500">Berdasarkan pesanan berstatus selesai.</p>
            </div>
            <div class="flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.1em] text-ink-500">
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-amber-400"></span> Penjualan</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-ink-950"></span> Komisi</span>
            </div>
        </div>

        <div class="mt-6 flex h-44 items-end gap-3">
            @foreach ($chart['labels'] as $index => $label)
                <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                    <div class="relative flex w-full max-w-12 items-end justify-center gap-1" style="height: 100%">
                        <div class="w-1/2 rounded-t-sm bg-amber-400 transition-all" style="height: {{ max(3, ($chart['revenue'][$index] / $maxRevenue) * 100) }}%" title="{{ format_rupiah($chart['revenue'][$index]) }}"></div>
                        <div class="w-1/3 rounded-t-sm bg-ink-950/80 transition-all" style="height: {{ max(2, ($chart['commission'][$index] / $maxRevenue) * 100) }}%" title="{{ format_rupiah($chart['commission'][$index]) }}"></div>
                    </div>
                    <span class="whitespace-nowrap text-[9px] font-semibold uppercase tracking-[0.08em] text-ink-400">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <div class="mt-4 grid gap-4 xl:grid-cols-[1.4fr_1fr]">
        {{-- Recent orders --}}
        <section class="card flex flex-col overflow-hidden">
            <div class="flex items-center justify-between border-b border-paper-200 px-5 py-4">
                <div>
                    <h2 class="text-[13px] font-semibold text-ink-950">Pesanan terbaru</h2>
                    <p class="mt-0.5 text-[11px] text-ink-500">Hanya pesanan yang sudah dibayar.</p>
                </div>
                <a href="{{ route('partner.orders.index') }}" class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-500 transition hover:text-ink-950">Lihat semua</a>
            </div>
            @if ($recentOrders->isEmpty())
                <div class="p-5">
                    <x-empty-state icon="shopping-bag" title="Belum ada pesanan" description="Pesanan baru akan muncul di sini setelah pelanggan menyelesaikan pembayaran." />
                </div>
            @else
                <div class="divide-y divide-paper-200">
                    @foreach ($recentOrders as $order)
                        <a href="{{ route('partner.orders.show', $order) }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-paper-50">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="tabular text-[12px] font-semibold text-ink-950">{{ $order->order_number }}</p>
                                    <span class="badge {{ $order->status->badge() }}">{{ $order->status->label() }}</span>
                                </div>
                                <p class="mt-1 text-[11px] text-ink-500">{{ $order->customer->name }} · {{ $order->items->sum('quantity') }} item · {{ format_tanggal($order->created_at, true) }}</p>
                            </div>
                            <p class="tabular shrink-0 text-[12px] font-bold text-ink-950">{{ format_rupiah($order->total) }}</p>
                            <x-icon name="chevron-right" class="h-3.5 w-3.5 shrink-0 text-paper-400" />
                        </a>
                    @endforeach
                </div>
                <div class="mt-auto flex items-center justify-between border-t border-paper-200 px-5 py-3">
                    <p class="text-[10.5px] text-ink-400">Menampilkan {{ $recentOrders->count() }} pesanan terbaru</p>
                    <a href="{{ route('partner.orders.index') }}" class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-[0.1em] text-ink-500 transition hover:text-ink-950">
                        Semua pesanan <x-icon name="arrow-right" class="h-3 w-3" />
                    </a>
                </div>
            @endif
        </section>

        <div class="grid gap-4">
            {{-- Low stock --}}
            <section class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-paper-200 px-5 py-4">
                    <div>
                        <h2 class="text-[13px] font-semibold text-ink-950">Stok menipis</h2>
                        <p class="mt-0.5 text-[11px] text-ink-500">Produk dengan stok ≤ 3.</p>
                    </div>
                    <a href="{{ route('partner.products.index') }}" class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-500 transition hover:text-ink-950">Kelola</a>
                </div>
                @if ($lowStockProducts->isEmpty())
                    <p class="px-5 py-8 text-center text-[11px] text-ink-400">Semua produk memiliki stok yang cukup.</p>
                @else
                    <ul class="divide-y divide-paper-200">
                        @foreach ($lowStockProducts as $product)
                            <li class="flex items-center gap-3 px-5 py-3">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-9 w-9 rounded-md border border-paper-200 object-cover">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[11.5px] font-semibold text-ink-950">{{ $product->name }}</p>
                                    <p class="tabular text-[10.5px] text-ink-500">{{ format_rupiah($product->price) }}</p>
                                </div>
                                <span class="badge {{ $product->stock <= 0 ? 'bg-rose-50 text-rose-700 ring-rose-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">{{ $product->stock }} unit</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Reviews --}}
            <section class="card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-[13px] font-semibold text-ink-950">Ulasan pelanggan</h2>
                        <p class="mt-0.5 text-[11px] text-ink-500">Reputasi toko Anda.</p>
                    </div>
                    <span class="badge {{ $store->status->badge() }}">{{ $store->status->label() }}</span>
                </div>

                <div class="mt-4 flex items-end gap-3 border-t border-paper-200 pt-4">
                    <p class="tabular font-sans text-[34px] font-semibold leading-none text-ink-950">{{ number_format($reviewAverage, 1, ',', '.') }}</p>
                    <div class="pb-0.5">
                        <x-star-rating :rating="$reviewAverage" size="h-3 w-3" />
                        <p class="mt-1 text-[10.5px] text-ink-500">{{ $stats['reviews'] }} ulasan dari seluruh produk</p>
                    </div>
                </div>

                @if ($latestReviews->isNotEmpty())
                    <div class="mt-4 space-y-3 border-t border-paper-200 pt-4">
                        @foreach ($latestReviews as $review)
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-[11px] font-semibold text-ink-900">{{ $review->customer->name }}</p>
                                    <x-star-rating :rating="$review->rating" size="h-2.5 w-2.5" />
                                </div>
                                @if ($review->comment)
                                    <p class="mt-1 line-clamp-2 text-[10.5px] leading-5 text-ink-500">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <a href="{{ route('partner.reviews.index') }}" class="btn-outline btn-sm mt-4 w-full">Lihat &amp; balas ulasan</a>
            </section>
        </div>
    </div>
</x-layouts.portal>
