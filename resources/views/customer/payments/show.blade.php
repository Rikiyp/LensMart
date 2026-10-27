<x-account-shell title="Pembayaran" subtitle="Selesaikan pembayaran simulasi untuk memproses pesanan Anda.">
    @php
        $expiresAt = $payment->expires_at?->getTimestampMs() ?? now()->addHour()->getTimestampMs();
        $paid = $payment->isPaid();
        $expired = $payment->isExpired();
    @endphp

    <div x-data="{
            paid: @js($paid),
            expiresAt: {{ $expiresAt }},
            remaining: 0,
            timer: null,
            poll: null,
            init() {
                this.tick();
                this.timer = setInterval(() => this.tick(), 1000);
                this.poll = setInterval(() => this.checkStatus(), 5000);
            },
            tick() {
                this.remaining = Math.max(0, Math.floor((this.expiresAt - Date.now()) / 1000));
            },
            get countdown() {
                const h = String(Math.floor(this.remaining / 3600)).padStart(2, '0');
                const m = String(Math.floor((this.remaining % 3600) / 60)).padStart(2, '0');
                const s = String(this.remaining % 60).padStart(2, '0');
                return h + ':' + m + ':' + s;
            },
            async checkStatus() {
                if (this.paid) return;
                try {
                    const { data } = await window.axios.get(@js(route('customer.payments.status', $payment)));
                    if (data.paid) {
                        this.paid = true;
                        window.location.reload();
                    }
                } catch (error) {}
            }
        }">
        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="space-y-5">
                {{-- Status --}}
                <div class="card overflow-hidden">
                    <div class="flex flex-wrap items-center gap-3 border-b border-paper-200 bg-paper-50/60 px-5 py-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Kode pembayaran</p>
                            <p class="font-mono text-sm font-bold text-ink-950">{{ $payment->code }}</p>
                        </div>
                        <span class="badge ml-auto {{ $payment->status->badge() }}">{{ $payment->status->label() }}</span>
                    </div>

                    @if ($paid)
                        <div class="p-6">
                            <div class="flex items-start gap-4 rounded-2xl bg-emerald-50 p-5 ring-1 ring-inset ring-emerald-600/20">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
                                    <x-icon name="check" class="h-6 w-6" />
                                </span>
                                <div>
                                    <p class="text-base font-extrabold text-emerald-900">Pembayaran berhasil!</p>
                                    <p class="mt-1 text-sm text-emerald-700">Dibayar pada {{ format_tanggal($payment->paid_at, true) }}. Pesanan Anda sudah diteruskan ke masing-masing toko.</p>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <a href="{{ route('customer.orders.index') }}" class="btn-primary btn-sm">Lihat pesanan</a>
                                        <a href="{{ route('products.index') }}" class="btn-outline btn-sm">Belanja lagi</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif ($expired)
                        <div class="p-6">
                            <div class="flex items-start gap-4 rounded-2xl bg-rose-50 p-5 ring-1 ring-inset ring-rose-600/20">
                                <x-icon name="clock" class="h-8 w-8 shrink-0 text-rose-500" />
                                <div>
                                    <p class="text-base font-extrabold text-rose-900">Pembayaran kedaluwarsa</p>
                                    <p class="mt-1 text-sm text-rose-700">Batas waktu pembayaran telah lewat. Stok sudah dikembalikan dan pesanan dibatalkan. Silakan buat pesanan baru.</p>
                                    <a href="{{ route('customer.cart.index') }}" class="btn-primary btn-sm mt-4">Kembali ke keranjang</a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-5">
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-amber-50 px-4 py-3 text-xs text-amber-900 ring-1 ring-inset ring-amber-600/20">
                                <span class="flex items-center gap-2"><x-icon name="clock" class="h-4 w-4" /> Selesaikan dalam</span>
                                <span class="font-mono text-sm font-extrabold" x-text="countdown"></span>
                            </div>

                            @if (! $payment->method)
                                <h2 class="mt-5 text-sm font-extrabold text-ink-950">Pilih metode pembayaran</h2>
                                <p class="mt-1 text-xs text-ink-500">Semua metode di bawah ini disimulasikan.</p>

                                <form method="POST" action="{{ route('customer.payments.method', $payment) }}" class="mt-4 space-y-4">
                                    @csrf
                                    <div class="grid gap-2 sm:grid-cols-3">
                                        @foreach ($methods as $key => $method)
                                            <label class="flex cursor-pointer flex-col gap-2 rounded-xl border border-paper-300 p-4 transition hover:border-amber-300 has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50/40">
                                                <input type="radio" name="method" value="{{ $key }}" required class="border-paper-400 text-amber-500 focus:ring-amber-400" @checked(old('method') === $key)>
                                                <x-icon :name="$method['icon']" class="h-6 w-6 text-ink-700" />
                                                <span>
                                                    <span class="block text-xs font-bold text-ink-950">{{ $method['label'] }}</span>
                                                    <span class="mt-0.5 block text-[10px] leading-snug text-ink-400">{{ $method['description'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <x-input-error :messages="$errors->get('method')" />
                                    <button type="submit" class="btn-primary w-full">Pilih metode pembayaran</button>
                                </form>
                            @else
                                <div class="mt-5 rounded-2xl border border-dashed border-paper-400 bg-paper-50 p-5 text-center">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">{{ $payment->method_label }} · {{ $payment->gateway }}</p>

                                    @if ($payment->method === 'qris')
                                        <div class="mx-auto mt-4 flex h-40 w-40 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-paper-300">
                                            <x-icon name="qr-code" class="h-24 w-24 text-ink-950" />
                                        </div>
                                        <p class="mt-3 text-xs text-ink-500">QRIS simulasi — pindai dengan aplikasi pembayaran.</p>
                                    @elseif ($payment->method === 'ewallet')
                                        <p class="mt-3 text-2xl font-extrabold tracking-tight text-ink-950">{{ $payment->gateway_reference }}</p>
                                        <p class="mt-1 text-xs text-ink-500">Kode pembayaran e-wallet simulasi.</p>
                                    @else
                                        <p class="mt-3 text-2xl font-extrabold tracking-wide text-ink-950">{{ $payment->gateway_reference }}</p>
                                        <p class="mt-1 text-xs text-ink-500">Nomor virtual account simulasi.</p>
                                    @endif

                                    <p class="mt-4 text-sm font-bold text-ink-950">Total: {{ format_rupiah($payment->amount) }}</p>
                                </div>

                                <form method="POST" action="{{ route('customer.payments.pay', $payment) }}" class="mt-4" x-confirm="{ title: 'Simulasikan pembayaran berhasil?', message: 'Status pembayaran akan diubah menjadi lunas dan pesanan diteruskan ke toko.', confirm: 'Ya, simulasikan', tone: 'primary' }">
                                    @csrf
                                    <button type="submit" class="btn-accent w-full py-3">
                                        <x-icon name="check-circle" class="h-4 w-4" /> Simulasikan Pembayaran Berhasil
                                    </button>
                                </form>
                                <a href="{{ route('customer.payments.show', $payment) }}" class="mt-2 block text-center text-[11px] font-semibold text-ink-400 hover:text-ink-950">Ganti metode pembayaran</a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Orders --}}
                <div class="card overflow-hidden">
                    <div class="border-b border-paper-200 px-5 py-4">
                        <h2 class="text-sm font-extrabold text-ink-950">Pesanan dalam transaksi ini</h2>
                        <p class="text-xs text-ink-500">{{ $payment->orders->count() }} pesanan dari {{ $payment->orders->unique('store_id')->count() }} toko.</p>
                    </div>
                    <div class="divide-y divide-paper-200">
                        @foreach ($payment->orders as $order)
                            <div class="p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('customer.orders.show', $order) }}" class="text-sm font-bold text-ink-950 hover:text-amber-600">{{ $order->order_number }}</a>
                                    <span class="badge {{ $order->status->badge() }}">{{ $order->status->label() }}</span>
                                    <span class="ml-auto text-sm font-extrabold text-ink-950">{{ format_rupiah($order->total) }}</span>
                                </div>
                                <p class="mt-1 text-xs text-ink-500">{{ $order->store->name }} · {{ $order->items->sum('quantity') }} item</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($order->items as $item)
                                        <span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $item->product_name }} × {{ $item->quantity }}</span>
                                    @endforeach
                                </div>
                                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-[11px] text-ink-400">
                                    <span>Subtotal {{ format_rupiah($order->subtotal) }}</span>
                                    <span>Ongkir {{ format_rupiah($order->shipping_cost) }}</span>
                                    <span>{{ $order->shipping_label }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <aside class="h-fit space-y-4 lg:sticky lg:top-24">
                <div class="card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Total tagihan</p>
                    <p class="mt-1 text-3xl font-extrabold tracking-tight text-ink-950">{{ format_rupiah($payment->amount) }}</p>
                    <div class="mt-4 space-y-2 border-t border-paper-200 pt-4 text-xs">
                        <div class="flex justify-between"><span class="text-ink-500">Metode</span><span class="font-semibold text-ink-900">{{ $payment->method_label }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-500">Gateway</span><span class="font-semibold text-ink-900">{{ ucfirst($payment->gateway) }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-500">Dibuat</span><span class="font-semibold text-ink-900">{{ format_tanggal($payment->created_at, true) }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-500">Batas bayar</span><span class="font-semibold text-ink-900">{{ format_tanggal($payment->expires_at, true) }}</span></div>
                    </div>
                </div>

                <div class="rounded-2xl bg-sky-50 p-4 text-[11px] leading-relaxed text-sky-900 ring-1 ring-inset ring-sky-600/20">
                    <p class="flex items-start gap-2">
                        <x-icon name="information-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>Ini pembayaran simulasi. Tidak ada uang yang benar-benar ditransfer. Gunakan tombol “Simulasikan Pembayaran Berhasil” untuk melanjutkan alur.</span>
                    </p>
                </div>

                <a href="{{ route('customer.orders.index') }}" class="btn-outline w-full">Kembali ke pesanan</a>
            </aside>
        </div>
    </div>
</x-account-shell>
