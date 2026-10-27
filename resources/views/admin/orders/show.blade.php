<x-layouts.portal :title="$order->order_number" subtitle="Detail transaksi lintas toko dan riwayat status.">
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-500 hover:text-amber-600">
            <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Semua pesanan
        </a>
        <span class="badge {{ $order->status->badge() }}">{{ $order->status->label() }}</span>
        <span class="text-xs text-ink-500">{{ format_tanggal($order->created_at, true) }}</span>
        @if ($order->payout)
            <span class="badge {{ $order->payout->status->badge() }}">Pencairan: {{ $order->payout->status->label() }}</span>
        @endif
    </div>

    <div class="grid gap-5 xl:grid-cols-[1fr_340px]">
        <div class="space-y-5">
            <section class="card overflow-hidden">
                <div class="grid gap-4 border-b border-paper-200 p-5 sm:grid-cols-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Pelanggan</p>
                        <p class="mt-1 text-sm font-bold text-ink-950">{{ $order->customer?->name }}</p>
                        <p class="text-xs text-ink-500">{{ $order->customer?->email }}</p>
                        <p class="text-xs text-ink-500">{{ $order->customer?->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Toko</p>
                        <a href="{{ $order->store ? route('admin.partners.show', $order->store) : '#' }}" class="mt-1 block text-sm font-bold text-ink-950 hover:text-amber-600">{{ $order->store?->name }}</a>
                        <p class="text-xs text-ink-500">{{ $order->store?->city ?? '-' }}</p>
                        <p class="text-xs text-ink-500">{{ $order->store?->user?->email }}</p>
                    </div>
                </div>

                <div class="border-b border-paper-200 px-5 py-4">
                    <h2 class="text-sm font-extrabold text-ink-950">Item pesanan</h2>
                </div>
                <div class="divide-y divide-paper-200">
                    @foreach ($order->items as $item)
                        <div class="flex gap-4 px-5 py-4">
                            @if ($item->product)
                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="h-16 w-16 rounded-xl object-cover ring-1 ring-paper-300">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-paper-100 text-paper-400"><x-icon name="photo" class="h-6 w-6" /></div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-sm font-bold text-ink-950">{{ $item->product_name }}</p>
                                <p class="mt-0.5 text-[11px] text-ink-500">{{ $item->quantity }} × {{ format_rupiah($item->price) }}</p>
                                @if ($item->product)
                                    <p class="mt-1 text-[10px] text-ink-400">{{ $item->product->brand?->name }} · {{ $item->product->model_name }}</p>
                                @endif
                            </div>
                            <p class="shrink-0 text-sm font-extrabold text-ink-950">{{ format_rupiah($item->subtotal) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="card overflow-hidden">
                <div class="border-b border-paper-200 px-5 py-4">
                    <h2 class="text-sm font-extrabold text-ink-950">Riwayat status</h2>
                </div>
                <ol class="divide-y divide-paper-200">
                    @forelse ($order->histories as $history)
                        <li class="flex gap-3 px-5 py-3.5">
                            <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-amber-400"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-bold text-ink-950">{{ $history->status->label() }}</span>
                                    <span class="text-[10px] text-ink-400">{{ format_tanggal($history->created_at, true) }}</span>
                                </div>
                                @if ($history->note)
                                    <p class="mt-0.5 text-[11px] leading-relaxed text-ink-500">{{ $history->note }}</p>
                                @endif
                                <p class="mt-0.5 text-[10px] text-ink-400">oleh {{ $history->changer?->name ?? 'Sistem' }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="px-5 py-6 text-center text-xs text-ink-400">Belum ada riwayat.</li>
                    @endforelse
                </ol>
            </section>

            @if ($order->note)
                <section class="card p-5">
                    <h2 class="text-sm font-extrabold text-ink-950">Catatan pelanggan</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ $order->note }}</p>
                </section>
            @endif
        </div>

        <aside class="space-y-5">
            <section class="card p-5">
                <h2 class="text-sm font-extrabold text-ink-950">Ringkasan</h2>
                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-500">Subtotal</dt><dd class="font-semibold text-ink-950">{{ format_rupiah($order->subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Ongkir</dt><dd class="font-semibold text-ink-950">{{ format_rupiah($order->shipping_cost) }}</dd></div>
                    <div class="flex justify-between text-xs"><dt class="text-ink-400">Komisi platform</dt><dd class="font-semibold text-emerald-600">{{ format_rupiah($order->commission_amount) }}</dd></div>
                    <div class="flex justify-between text-xs"><dt class="text-ink-400">Pendapatan partner</dt><dd class="font-semibold text-ink-700">{{ format_rupiah($order->subtotal - $order->commission_amount) }}</dd></div>
                </dl>
                <div class="mt-4 flex items-end justify-between border-t border-paper-200 pt-4">
                    <span class="text-xs font-bold text-ink-500">Total dibayar</span>
                    <span class="text-xl font-extrabold text-ink-950">{{ format_rupiah($order->total) }}</span>
                </div>
                <div class="mt-3 rounded-xl bg-paper-50 px-3 py-2.5 text-[11px]">
                    <div class="flex justify-between"><span class="text-ink-500">Pembayaran</span><span class="font-semibold text-ink-900">{{ $order->payment?->method_label ?? '-' }}</span></div>
                    <div class="mt-1 flex justify-between"><span class="text-ink-500">Status</span><span><span class="badge {{ $order->payment?->status->badge() ?? 'bg-paper-100 text-ink-600 ring-ink-500/15' }}">{{ $order->payment?->status->label() ?? '-' }}</span></span></div>
                    @if ($order->payment?->paid_at)
                        <div class="mt-1 flex justify-between"><span class="text-ink-500">Dibayar</span><span class="font-semibold text-ink-900">{{ format_tanggal($order->payment->paid_at, true) }}</span></div>
                    @endif
                    @if ($order->payment?->gateway_reference)
                        <div class="mt-1 flex justify-between"><span class="text-ink-500">Referensi</span><span class="font-mono text-[10px] text-ink-500">{{ $order->payment->gateway_reference }}</span></div>
                    @endif
                </div>
            </section>

            <section class="card p-5">
                <h2 class="text-sm font-extrabold text-ink-950">Pengiriman</h2>
                <p class="mt-3 text-sm font-bold text-ink-950">{{ $order->recipient_name }}</p>
                <p class="text-xs text-ink-500">{{ $order->recipient_phone }}</p>
                <p class="mt-2 text-xs leading-relaxed text-ink-600">{{ $order->shipping_address }}</p>
                <p class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-ink-500"><x-icon name="truck" class="h-3.5 w-3.5 text-amber-500" /> {{ $order->shipping_label }}</p>
                @if ($order->tracking_number)
                    <p class="mt-2 text-[11px] text-ink-500">Resi: <span class="font-mono font-bold text-ink-900">{{ $order->tracking_number }}</span></p>
                @endif
            </section>

            @if ($order->payout)
                <section class="card p-5">
                    <h2 class="text-sm font-extrabold text-ink-950">Pencairan</h2>
                    <div class="mt-3 space-y-2 text-xs">
                        <div class="flex justify-between"><span class="text-ink-500">Jumlah</span><span class="font-extrabold text-ink-950">{{ format_rupiah($order->payout->amount) }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-500">Status</span><span class="badge {{ $order->payout->status->badge() }}">{{ $order->payout->status->label() }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-500">Dibayar</span><span class="text-ink-700">{{ $order->payout->paid_at ? format_tanggal($order->payout->paid_at, true) : '-' }}</span></div>
                    </div>
                </section>
            @endif

            <a href="{{ route('admin.orders.index') }}" class="btn-outline w-full">Kembali ke daftar</a>
        </aside>
    </div>
</x-layouts.portal>
