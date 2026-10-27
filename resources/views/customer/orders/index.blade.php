<x-account-shell title="Pesanan Saya" subtitle="Lacak status, lihat detail, dan kelola semua transaksi Anda.">
    @php
        $tabs = [
            ['value' => null, 'label' => 'Semua'],
            ...collect($statuses)->map(fn ($item) => ['value' => $item->value, 'label' => $item->label()])->all(),
        ];
    @endphp

    <div class="flex flex-wrap gap-2">
        @foreach ($tabs as $tab)
            <a href="{{ route('customer.orders.index', array_filter(['status' => $tab['value']])) }}"
               class="rounded-full px-4 py-2 text-xs font-bold transition {{ (string) $status === (string) $tab['value'] ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    @if ($orders->isEmpty())
        <div class="mt-6">
            <x-empty-state icon="shopping-bag" title="Belum ada pesanan" description="Pesanan yang Anda buat akan muncul di sini. Mulai jelajahi katalog untuk menemukan gear favorit.">
                <a href="{{ route('products.index') }}" class="btn-primary btn-sm">Belanja sekarang</a>
            </x-empty-state>
        </div>
    @else
        <div class="mt-6 space-y-4">
            @foreach ($orders as $order)
                <article class="card overflow-hidden">
                    <div class="flex flex-wrap items-center gap-3 border-b border-paper-200 bg-paper-50/60 px-5 py-3.5">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('customer.orders.show', $order) }}" class="text-sm font-extrabold text-ink-950 hover:text-amber-600">{{ $order->order_number }}</a>
                                <span class="badge {{ $order->status->badge() }}">{{ $order->status->label() }}</span>
                            </div>
                            <p class="mt-0.5 text-[11px] text-ink-500">{{ format_tanggal($order->created_at, true) }} · {{ $order->items->sum('quantity') }} item</p>
                        </div>
                        <div class="ml-auto flex items-center gap-3">
                            <span class="text-base font-extrabold text-ink-950">{{ format_rupiah($order->total) }}</span>
                            <a href="{{ route('customer.orders.show', $order) }}" class="btn-outline btn-sm">Detail</a>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-2">
                            <img src="{{ $order->store->logo_url }}" alt="{{ $order->store->name }}" class="h-9 w-9 rounded-lg object-cover">
                            <div>
                                <p class="flex items-center gap-1 text-xs font-bold text-ink-950">
                                    {{ $order->store->name }}
                                    <x-icon name="shield-check" class="h-3.5 w-3.5 text-emerald-500" />
                                </p>
                                <p class="text-[11px] text-ink-400">{{ $order->store->city }}</p>
                            </div>
                        </div>

                        <div class="flex flex-1 items-center gap-2 overflow-hidden">
                            @foreach ($order->items->take(3) as $item)
                                <a href="{{ $item->product ? route('products.show', $item->product) : '#' }}" class="group relative">
                                    <img src="{{ $item->product?->image_url ?? asset('images/placeholder-product.svg') }}" alt="{{ $item->product_name }}" class="h-14 w-14 rounded-xl border border-paper-300 object-cover">
                                    @if ($item->quantity > 1)
                                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-ink-950 px-1 text-[9px] font-bold text-white">×{{ $item->quantity }}</span>
                                    @endif
                                </a>
                            @endforeach
                            @if ($order->items->count() > 3)
                                <span class="text-[11px] font-semibold text-ink-400">+{{ $order->items->count() - 3 }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 sm:justify-end">
                            @if (! $order->isPaid() && $order->payment)
                                <a href="{{ route('customer.payments.show', $order->payment) }}" class="btn-accent btn-sm">
                                    <x-icon name="credit-card" class="h-3.5 w-3.5" /> Bayar sekarang
                                </a>
                            @endif
                            @if ($order->status->value === 'shipped')
                                <form method="POST" action="{{ route('customer.orders.confirm', $order) }}" x-confirm="{ title: 'Konfirmasi pesanan diterima?', message: 'Pesanan akan ditandai selesai dan dana diteruskan ke toko.', confirm: 'Ya, sudah diterima', tone: 'primary' }">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm">
                                        <x-icon name="check-circle" class="h-3.5 w-3.5" /> Pesanan diterima
                                    </button>
                                </form>
                            @endif
                            @if ($order->status->value === 'completed')
                                <a href="{{ route('products.index') }}" class="btn-outline btn-sm">Beli lagi</a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</x-account-shell>
