<x-layouts.portal :title="$order->order_number" subtitle="Detail dan proses pesanan pelanggan.">
    @php
        $next = $order->status->next();
        $canAdvance = $next !== null && $next !== \App\Enums\OrderStatus::Completed;
        $isCancellable = $order->status->isCancellable();
        $steps = \App\Enums\OrderStatus::progressSteps();
        $currentStep = $order->status === \App\Enums\OrderStatus::Cancelled ? 0 : $order->status_step;
    @endphp

    <div class="mb-5 flex flex-wrap items-center gap-3">
        <a href="{{ route('partner.orders.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-500 hover:text-amber-600">
            <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Semua pesanan
        </a>
        <span class="badge {{ $order->status->badge() }}">{{ $order->status->label() }}</span>
        <span class="text-xs text-ink-500">{{ format_tanggal($order->created_at, true) }}</span>
    </div>

    <div class="card mb-5 p-5">
        <ol class="grid grid-cols-1 gap-3 sm:grid-cols-5">
            @foreach ($steps as $index => $step)
                @php
                    $reached = ($index + 1) <= $currentStep;
                @endphp
                <li class="flex items-center gap-3 sm:flex-col sm:text-center">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[11px] font-extrabold ring-2 {{ $reached ? 'bg-amber-400 text-ink-950 ring-amber-400' : 'bg-white text-ink-400 ring-paper-300' }}">
                        @if ($reached)
                            <x-icon name="check" class="h-4 w-4" />
                        @else
                            {{ $index + 1 }}
                        @endif
                    </span>
                    <p class="text-xs font-bold {{ $reached ? 'text-ink-950' : 'text-ink-400' }}">{{ $step->label() }}</p>
                </li>
            @endforeach
        </ol>
        @if ($order->tracking_number)
            <div class="mt-4 flex items-center gap-2 rounded-xl bg-violet-50 px-4 py-3 text-xs text-violet-800 ring-1 ring-inset ring-violet-600/20">
                <x-icon name="truck" class="h-4 w-4" /> Nomor resi: <span class="font-mono font-bold">{{ $order->tracking_number }}</span>
            </div>
        @endif
    </div>

    <div class="grid gap-5 xl:grid-cols-[1fr_340px]">
        <div class="space-y-5">
            <section class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-paper-200 px-5 py-4">
                    <h2 class="text-sm font-extrabold text-ink-950">Item pesanan</h2>
                    <span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $order->items->sum('quantity') }} item</span>
                </div>
                <div class="divide-y divide-paper-200">
                    @foreach ($order->items as $item)
                        <div class="flex gap-4 px-5 py-4">
                            @if ($item->product)
                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="h-16 w-16 rounded-xl object-cover ring-1 ring-paper-300">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-paper-100 text-paper-400">
                                    <x-icon name="photo" class="h-6 w-6" />
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-sm font-bold text-ink-950">{{ $item->product_name }}</p>
                                <p class="mt-0.5 text-[11px] text-ink-500">{{ $item->quantity }} × {{ format_rupiah($item->price) }}</p>
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
                    @foreach ($order->histories as $history)
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
                            </div>
                        </li>
                    @endforeach
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
                    <div class="flex justify-between text-xs"><dt class="text-ink-400">Komisi platform (5%)</dt><dd class="text-rose-500">-{{ format_rupiah($order->commission_amount) }}</dd></div>
                    <div class="flex justify-between border-t border-paper-200 pt-3 text-xs"><dt class="font-semibold text-ink-600">Pendapatan bersih</dt><dd class="font-extrabold text-emerald-600">{{ format_rupiah($order->subtotal - $order->commission_amount) }}</dd></div>
                </dl>
                <div class="mt-4 flex items-end justify-between border-t border-paper-200 pt-4">
                    <span class="text-xs font-bold text-ink-500">Total dibayar</span>
                    <span class="text-xl font-extrabold text-ink-950">{{ format_rupiah($order->total) }}</span>
                </div>
                <p class="mt-2 text-[11px] text-ink-400">Metode: {{ $order->payment?->method_label ?? '-' }} · {{ $order->payment?->status->label() }}</p>
            </section>

            <section class="card p-5">
                <h2 class="text-sm font-extrabold text-ink-950">Pengiriman</h2>
                <p class="mt-3 text-sm font-bold text-ink-950">{{ $order->recipient_name }}</p>
                <p class="text-xs text-ink-500">{{ $order->recipient_phone }}</p>
                <p class="mt-2 text-xs leading-relaxed text-ink-600">{{ $order->shipping_address }}</p>
                <p class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-ink-500">
                    <x-icon name="truck" class="h-3.5 w-3.5 text-amber-500" /> {{ $order->shipping_label }}
                </p>
            </section>

            @if ($canAdvance || $isCancellable)
                <section class="card p-5">
                    <h2 class="text-sm font-extrabold text-ink-950">Proses pesanan</h2>

                    @if ($canAdvance)
                        <form method="POST" action="{{ route('partner.orders.status', $order) }}" class="mt-3 space-y-3">
                            @csrf
                            <input type="hidden" name="action" value="advance">
                            @if ($next === \App\Enums\OrderStatus::Shipped)
                                <div>
                                    <label for="tracking_number" class="label">Nomor resi <span class="text-rose-500">*</span></label>
                                    <input id="tracking_number" type="text" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}" required maxlength="100" class="input !py-2 text-xs" placeholder="Contoh: JNE123456789">
                                    <x-input-error :messages="$errors->get('tracking_number')" class="mt-1" />
                                </div>
                            @endif
                            <div>
                                <label for="note" class="label">Catatan <span class="font-normal text-ink-400">(opsional)</span></label>
                                <textarea id="note" name="note" rows="2" maxlength="500" class="textarea !min-h-0 text-xs" placeholder="Catatan untuk pelanggan">{{ old('note') }}</textarea>
                                <x-input-error :messages="$errors->get('status')" class="mt-1" />
                            </div>
                            <button type="submit" class="btn-primary w-full">
                                <x-icon name="arrow-right" class="h-4 w-4" /> Tandai {{ $next->label() }}
                            </button>
                        </form>
                    @endif

                    @if ($isCancellable)
                        <form method="POST" action="{{ route('partner.orders.status', $order) }}" class="mt-3 border-t border-paper-200 pt-3" x-confirm="{ title: 'Tolak pesanan ini?', message: 'Pesanan akan dibatalkan dan stok produk dikembalikan.', confirm: 'Ya, tolak', tone: 'danger' }">
                            @csrf
                            <input type="hidden" name="action" value="cancel">
                            <input type="text" name="note" maxlength="500" class="input !py-2 text-xs" placeholder="Alasan penolakan (opsional)">
                            <button type="submit" class="btn-outline mt-2 w-full text-rose-600 hover:bg-rose-50">
                                <x-icon name="x-mark" class="h-4 w-4" /> Tolak pesanan
                            </button>
                        </form>
                    @endif

                    <p class="mt-3 flex items-start gap-1.5 text-[10px] leading-relaxed text-ink-400">
                        <x-icon name="information-circle" class="mt-0.5 h-3 w-3 shrink-0" />
                        Pesanan ditandai selesai otomatis setelah pelanggan mengonfirmasi penerimaan.
                    </p>
                </section>
            @endif

            <a href="{{ route('partner.orders.index') }}" class="btn-outline w-full">Kembali ke daftar</a>
        </aside>
    </div>
</x-layouts.portal>
