<x-account-shell :title="$order->order_number" subtitle="Detail transaksi dan riwayat status pesanan Anda.">
    @php
        $isCancelled = $order->status === \App\Enums\OrderStatus::Cancelled;
        $steps = \App\Enums\OrderStatus::progressSteps();
        $currentStep = $isCancelled ? 0 : $order->status_step;
        $reviewByProduct = $order->reviews->keyBy('product_id');
    @endphp

    <div class="mb-5 flex flex-wrap items-center gap-3">
        <a href="{{ route('customer.orders.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-500 hover:text-amber-600">
            <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Semua pesanan
        </a>
        <span class="badge {{ $order->status->badge() }}">{{ $order->status->label() }}</span>
        @if (! $order->isPaid() && $order->payment)
            <a href="{{ route('customer.payments.show', $order->payment) }}" class="badge bg-amber-50 text-amber-700 ring-amber-600/20 hover:bg-amber-100">
                <x-icon name="clock" class="h-3 w-3" /> Menunggu pembayaran
            </a>
        @endif
    </div>

    {{-- Progress --}}
    <div class="card p-5">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-ink-950">Status pesanan</h2>
            <p class="text-[11px] text-ink-400">Diperbarui {{ format_tanggal($order->updated_at, true) }}</p>
        </div>

        @if ($isCancelled)
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-rose-800 ring-1 ring-inset ring-rose-600/20">
                <x-icon name="exclamation-circle" class="mt-0.5 h-5 w-5 shrink-0" />
                <div>
                    <p class="text-sm font-bold">Pesanan dibatalkan</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-rose-700">Stok produk sudah dikembalikan. Silakan buat pesanan baru jika masih membutuhkan produk ini.</p>
                </div>
            </div>
        @else
            <ol class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-5">
                @foreach ($steps as $index => $step)
                    @php
                        $reached = ($index + 1) <= $currentStep;
                    @endphp
                    <li class="relative flex gap-3 sm:flex-col sm:items-center sm:text-center">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[11px] font-extrabold ring-2
                            {{ $reached ? 'bg-amber-400 text-ink-950 ring-amber-400' : 'bg-white text-ink-400 ring-paper-300' }}">
                            @if ($reached)
                                <x-icon name="check" class="h-4 w-4" />
                            @else
                                {{ $index + 1 }}
                            @endif
                        </span>
                        <div class="sm:mt-1">
                            <p class="text-xs font-bold {{ $reached ? 'text-ink-950' : 'text-ink-400' }}">{{ $step->label() }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            @if ($order->status === \App\Enums\OrderStatus::Shipped && $order->tracking_number)
                <div class="mt-4 flex items-center gap-2 rounded-xl bg-violet-50 px-4 py-3 text-xs text-violet-800 ring-1 ring-inset ring-violet-600/20">
                    <x-icon name="truck" class="h-4 w-4" />
                    Nomor resi: <span class="font-mono font-bold">{{ $order->tracking_number }}</span>
                </div>
            @endif
        @endif
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_320px]">
        <div class="space-y-5">
            {{-- Items --}}
            <section class="card overflow-hidden">
                <div class="flex items-center gap-3 border-b border-paper-200 px-5 py-4">
                    <img src="{{ $order->store->logo_url }}" alt="{{ $order->store->name }}" class="h-9 w-9 rounded-lg object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-1.5 text-sm font-bold text-ink-950">
                            {{ $order->store->name }}
                            <x-icon name="shield-check" class="h-3.5 w-3.5 text-emerald-500" />
                        </p>
                        <p class="text-[11px] text-ink-400">{{ $order->store->city }}</p>
                    </div>
                    <span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $order->items->sum('quantity') }} item</span>
                </div>

                <div class="divide-y divide-paper-200">
                    @foreach ($order->items as $item)
                        @php
                            $review = $reviewByProduct->get($item->product_id);
                        @endphp
                        <div class="flex gap-4 p-5">
                            @if ($item->product)
                                <a href="{{ route('products.show', $item->product) }}" class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-paper-100">
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover">
                                </a>
                            @else
                                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-paper-100 text-paper-400">
                                    <x-icon name="photo" class="h-8 w-8" />
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">{{ $item->product?->brand?->name }}</p>
                                <p class="line-clamp-2 text-sm font-bold leading-snug text-ink-950">{{ $item->product_name }}</p>
                                <p class="mt-1 text-[11px] text-ink-500">{{ $item->quantity }} × {{ format_rupiah($item->price) }}</p>
                                <p class="mt-2 text-sm font-extrabold text-ink-950">{{ format_rupiah($item->subtotal) }}</p>

                                @if ($canReview)
                                    <div class="mt-2">
                                        @if ($review)
                                            <a href="{{ route('products.show', $review->product) }}#ulasan" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:text-emerald-700">
                                                <x-icon name="check-circle" class="h-3.5 w-3.5" /> Ulasan sudah dikirim
                                            </a>
                                        @elseif ($item->product)
                                            <a href="{{ route('customer.reviews.create', [$order, $item->product]) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 hover:text-amber-700">
                                                <x-icon name="star" class="h-3.5 w-3.5" /> Beri ulasan
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- History --}}
            <section class="card overflow-hidden">
                <div class="border-b border-paper-200 px-5 py-4">
                    <h2 class="text-sm font-extrabold text-ink-950">Riwayat status</h2>
                </div>
                <ol class="divide-y divide-paper-200">
                    @forelse ($order->histories as $history)
                        <li class="flex gap-3 px-5 py-4">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-paper-100 text-ink-500">
                                <x-icon name="arrow-path" class="h-3.5 w-3.5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="badge {{ $history->status->badge() }}">{{ $history->status->label() }}</span>
                                    <span class="text-[11px] text-ink-400">{{ format_tanggal($history->created_at, true) }}</span>
                                </div>
                                @if ($history->note)
                                    <p class="mt-1.5 text-xs leading-relaxed text-ink-600">{{ $history->note }}</p>
                                @endif
                                <p class="mt-1 text-[10px] text-ink-400">oleh {{ $history->changer?->name ?? 'Sistem' }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="px-5 py-6 text-center text-xs text-ink-400">Belum ada riwayat status.</li>
                    @endforelse
                </ol>
            </section>

            @if ($order->note)
                <section class="card p-5">
                    <h2 class="text-sm font-extrabold text-ink-950">Catatan pesanan</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ $order->note }}</p>
                </section>
            @endif
        </div>

        <aside class="space-y-5">
            {{-- Summary --}}
            <section class="card p-5">
                <h2 class="text-sm font-extrabold text-ink-950">Ringkasan pembayaran</h2>
                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Subtotal produk</dt>
                        <dd class="font-semibold text-ink-950">{{ format_rupiah($order->subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Ongkir ({{ $order->shipping_label }})</dt>
                        <dd class="font-semibold text-ink-950">{{ format_rupiah($order->shipping_cost) }}</dd>
                    </div>
                    <div class="flex justify-between text-xs text-ink-400">
                        <dt>Komisi platform (5%)</dt>
                        <dd>Ditanggung penjual</dd>
                    </div>
                </dl>
                <div class="mt-4 flex items-end justify-between border-t border-paper-200 pt-4">
                    <span class="text-xs font-bold text-ink-500">Total</span>
                    <span class="text-xl font-extrabold text-ink-950">{{ format_rupiah($order->total) }}</span>
                </div>
                <div class="mt-3 rounded-xl bg-paper-50 px-3 py-2.5 text-[11px]">
                    <div class="flex justify-between"><span class="text-ink-500">Pembayaran</span><span class="font-semibold text-ink-900">{{ $order->payment?->method_label ?? '-' }}</span></div>
                    <div class="mt-1 flex justify-between"><span class="text-ink-500">Status</span><span class="font-semibold text-ink-900">{{ $order->payment?->status->label() ?? '-' }}</span></div>
                    @if ($order->payment?->paid_at)
                        <div class="mt-1 flex justify-between"><span class="text-ink-500">Dibayar</span><span class="font-semibold text-ink-900">{{ format_tanggal($order->payment->paid_at, true) }}</span></div>
                    @endif
                </div>
                @if (! $order->isPaid() && $order->payment)
                    <a href="{{ route('customer.payments.show', $order->payment) }}" class="btn-accent mt-4 w-full">Selesaikan pembayaran</a>
                @endif
            </section>

            {{-- Shipping --}}
            <section class="card p-5">
                <h2 class="text-sm font-extrabold text-ink-950">Alamat pengiriman</h2>
                <div class="mt-3 text-sm">
                    <p class="font-bold text-ink-950">{{ $order->recipient_name }}</p>
                    <p class="mt-0.5 text-xs text-ink-500">{{ $order->recipient_phone }}</p>
                    <p class="mt-2 text-xs leading-relaxed text-ink-600">{{ $order->shipping_address }}</p>
                    <p class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-ink-500">
                        <x-icon name="truck" class="h-3.5 w-3.5 text-amber-500" /> {{ $order->shipping_label }}
                    </p>
                </div>
            </section>

            {{-- Actions --}}
            @if ($order->status->isCancellable() || $order->status === \App\Enums\OrderStatus::Shipped)
                <section class="card p-5">
                    <h2 class="text-sm font-extrabold text-ink-950">Tindakan</h2>

                    @if ($order->status === \App\Enums\OrderStatus::Shipped)
                        <p class="mt-1 text-xs leading-relaxed text-ink-500">Sudah menerima paket? Konfirmasi agar pesanan ditandai selesai dan dana diteruskan ke toko.</p>
                        <form method="POST" action="{{ route('customer.orders.confirm', $order) }}" class="mt-3" x-confirm="{ title: 'Konfirmasi pesanan diterima?', message: 'Pesanan akan ditandai selesai dan dana diteruskan ke toko.', confirm: 'Ya, sudah diterima', tone: 'primary' }">
                            @csrf
                            <button type="submit" class="btn-primary w-full">
                                <x-icon name="check-circle" class="h-4 w-4" /> Pesanan diterima
                            </button>
                        </form>
                    @endif

                    @if ($order->status->isCancellable())
                        <p class="mt-1 text-xs leading-relaxed text-ink-500">Pesanan belum dikonfirmasi toko. Anda masih dapat membatalkannya.</p>
                        <form method="POST" action="{{ route('customer.orders.cancel', $order) }}" class="mt-3 space-y-2" x-confirm="{ title: 'Batalkan pesanan?', message: 'Pesanan akan dibatalkan dan stok produk dikembalikan. Tindakan ini tidak dapat dibatalkan.', confirm: 'Ya, batalkan', tone: 'danger' }">
                            @csrf
                            <input type="text" name="note" maxlength="500" placeholder="Alasan pembatalan (opsional)" class="input !py-2 text-xs">
                            <button type="submit" class="btn-outline w-full text-rose-600 hover:bg-rose-50">
                                <x-icon name="x-mark" class="h-4 w-4" /> Batalkan pesanan
                            </button>
                        </form>
                    @endif
                </section>
            @endif
        </aside>
    </div>
</x-account-shell>
