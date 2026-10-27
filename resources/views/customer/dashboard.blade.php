<x-account-shell title="Ringkasan" subtitle="Pantau pesanan, wishlist, dan aktivitas belanja Anda.">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['shopping-bag', 'Total pesanan', $stats['orders'], 'semua waktu'],
            ['clock', 'Menunggu bayar', $stats['unpaid'], 'selesaikan segera'],
            ['check-circle', 'Pesanan selesai', $stats['completed'], 'terima kasih'],
            ['heart', 'Wishlist', $stats['wishlist'], 'produk disimpan'],
        ] as $card)
            @php
                [$icon, $label, $value, $hint] = $card;
            @endphp
            <div class="stat-card">
                <div class="flex items-center justify-between gap-3">
                    <p class="field-label">{{ $label }}</p>
                    <x-icon :name="$icon" class="h-4 w-4 text-ink-400" />
                </div>
                <p class="tabular mt-3 font-sans text-[28px] font-semibold leading-none text-ink-950">{{ $value }}</p>
                <p class="text-[10.5px] uppercase tracking-[0.1em] text-ink-400">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    <div class="card mt-5 overflow-hidden">
        <div class="flex items-center justify-between border-b border-paper-200 px-5 py-4">
            <div>
                <h2 class="text-[13px] font-semibold text-ink-950">Pesanan terbaru</h2>
                <p class="mt-0.5 text-[11px] text-ink-500">Lima transaksi terakhir Anda.</p>
            </div>
            <a href="{{ route('customer.orders.index') }}" class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-500 transition hover:text-ink-950">Lihat semua</a>
        </div>

        @if ($orders->isEmpty())
            <div class="p-6">
                <x-empty-state icon="shopping-bag" title="Belum ada pesanan" description="Mulai jelajahi katalog dan temukan gear pertama Anda.">
                    <a href="{{ route('products.index') }}" class="btn-primary btn-sm">Belanja sekarang</a>
                </x-empty-state>
            </div>
        @else
            <div class="divide-y divide-paper-200">
                @foreach ($orders as $order)
                    <a href="{{ route('customer.orders.show', $order) }}" class="flex flex-col gap-3 px-5 py-3.5 transition hover:bg-paper-50 sm:flex-row sm:items-center">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="tabular text-[12px] font-semibold text-ink-950">{{ $order->order_number }}</p>
                                <span class="badge {{ $order->status->badge() }}">{{ $order->status->label() }}</span>
                                @if (! $order->isPaid())
                                    <span class="badge bg-rose-50 text-rose-700 ring-rose-600/20">Belum dibayar</span>
                                @endif
                            </div>
                            <p class="mt-1 text-[11px] text-ink-500">{{ $order->store->name }} · {{ $order->items->sum('quantity') }} item · {{ format_tanggal($order->created_at, true) }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-4 sm:justify-end">
                            <p class="tabular text-[12px] font-bold text-ink-950">{{ format_rupiah($order->total) }}</p>
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-[0.1em] text-ink-400">
                                Detail <x-icon name="arrow-right" class="h-3 w-3" />
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if ($featuredProducts->isNotEmpty())
        <div class="mt-8">
            <div class="mb-5 flex items-end justify-between gap-4 border-b border-paper-300 pb-5">
                <div>
                    <p class="section-eyebrow">Katalog</p>
                    <h2 class="mt-3 font-display text-[26px] font-semibold tracking-[-0.02em] text-ink-950">Baru di LensMart</h2>
                </div>
                <a href="{{ route('products.index') }}" class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-500 transition hover:text-ink-950">Semua produk</a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    @endif
</x-account-shell>
