<x-account-shell title="Keranjang" subtitle="Pilih produk yang ingin dibeli, lalu lanjutkan ke pembayaran.">
    @if ($groups->isEmpty())
        <x-empty-state icon="shopping-cart" title="Keranjang Anda kosong" description="Tambahkan kamera, lensa, atau aksesori favorit untuk memulai belanja.">
            <a href="{{ route('products.index') }}" class="btn-primary btn-sm">Jelajahi katalog</a>
        </x-empty-state>
    @else
        <form id="cart-checkout-form" method="POST" action="{{ route('customer.cart.checkout') }}">
            @csrf
        </form>

        <div class="grid gap-6 lg:grid-cols-[1fr_320px]"
             x-data="{
                 selected: @js($items->pluck('id')->values()),
                 items: @js($itemData),
                 init() {
                     const stored = JSON.parse(sessionStorage.getItem('cart-selection') || '[]');
                     const valid = Array.isArray(stored)
                         ? stored.map(Number).filter(id => this.items[id] !== undefined)
                         : [];
                     if (valid.length > 0) this.selected = valid;
                     this.$watch('selected', value => sessionStorage.setItem('cart-selection', JSON.stringify(value)));
                 },
                 rupiah(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value)); },
                 isSelected(id) { return this.selected.map(Number).includes(Number(id)); },
                 allSelected(ids) { return ids.length > 0 && ids.every(id => this.isSelected(id)); },
                 someSelected(ids) { return ids.some(id => this.isSelected(id)); },
                 toggleStore(ids, checked) {
                     const next = new Set(this.selected.map(Number));
                     ids.forEach(id => checked ? next.add(Number(id)) : next.delete(Number(id)));
                     this.selected = [...next];
                 },
                 get selectedCount() {
                     return this.selected.reduce((total, id) => total + (this.items[id]?.quantity ?? 0), 0);
                 },
                 get selectedSubtotal() {
                     return this.selected.reduce((total, id) => total + (this.items[id]?.subtotal ?? 0), 0);
                 }
             }">
            <div class="space-y-5">
                @foreach ($groups as $storeId => $storeItems)
                    @php
                        $store = $storeItems->first()->product->store;
                        $storeItemIds = $storeItems->pluck('id')->values();
                    @endphp
                    <div class="card overflow-hidden">
                        <div class="flex items-center gap-3 border-b border-paper-200 px-4 py-3">
                            <label class="flex h-8 w-6 shrink-0 cursor-pointer items-center justify-center" title="Pilih semua produk dari {{ $store->name }}">
                                <input type="checkbox"
                                       class="h-4 w-4 rounded border-paper-400 text-ink-950 focus:ring-amber-400"
                                       :checked="allSelected(@js($storeItemIds))"
                                       x-effect="$el.indeterminate = someSelected(@js($storeItemIds)) && !allSelected(@js($storeItemIds))"
                                       @change="toggleStore(@js($storeItemIds), $event.target.checked)"
                                       aria-label="Pilih semua produk dari {{ $store->name }}">
                            </label>
                            <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="h-8 w-8 rounded-lg object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="flex items-center gap-1.5 truncate text-sm font-bold text-ink-950">
                                    {{ $store->name }}
                                    <x-icon name="shield-check" class="h-3.5 w-3.5 text-emerald-500" />
                                </p>
                                <p class="text-[11px] text-ink-400">{{ $store->city }}</p>
                            </div>
                            <span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $storeItems->sum('quantity') }} item</span>
                        </div>

                        <div class="divide-y divide-paper-200">
                            @foreach ($storeItems as $item)
                                @php
                                    $product = $item->product;
                                @endphp
                                <div class="flex gap-3 p-4 transition sm:gap-4"
                                     :class="isSelected({{ $item->id }}) ? 'bg-amber-50/30' : ''">
                                    <label class="flex cursor-pointer items-start pt-1">
                                        <input type="checkbox"
                                               form="cart-checkout-form"
                                               name="items[]"
                                               value="{{ $item->id }}"
                                               x-model="selected"
                                               class="h-4 w-4 rounded border-paper-400 text-ink-950 focus:ring-amber-400"
                                               aria-label="Pilih {{ $product->name }}">
                                    </label>

                                    <a href="{{ route('products.show', $product) }}" class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-paper-100">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3 max-sm:flex-col max-sm:gap-1.5">
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">{{ $product->brand?->name }}</p>
                                                <a href="{{ route('products.show', $product) }}" class="line-clamp-2 text-sm font-bold leading-snug text-ink-950 hover:text-amber-600">{{ $product->name }}</a>
                                                <p class="mt-1 text-[11px] text-ink-500">{{ format_rupiah($product->price) }} · Stok {{ $product->stock }}</p>
                                            </div>
                                            <p class="shrink-0 text-sm font-extrabold text-ink-950">{{ format_rupiah($item->subtotal) }}</p>
                                        </div>

                                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                            <form method="POST" action="{{ route('customer.cart.update', $item) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $product->stock }}"
                                                       class="input w-16 !py-1.5 text-center text-xs" aria-label="Jumlah">
                                                <button type="submit" class="btn-outline btn-sm !py-1.5">Perbarui</button>
                                            </form>

                                            <form method="POST" action="{{ route('customer.cart.destroy', $item) }}" x-confirm="{ title: 'Hapus dari keranjang?', message: 'Produk ini akan dihapus dari keranjang belanja.', confirm: 'Ya, hapus', tone: 'danger' }">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 text-[11px] font-semibold text-ink-400 transition hover:text-rose-500">
                                                    <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-500 hover:text-amber-600">
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Lanjut belanja
                </a>
            </div>

            <aside class="h-fit lg:sticky lg:top-24">
                <div class="card p-5">
                    <h2 class="text-sm font-extrabold text-ink-950">Ringkasan belanja</h2>
                    <dl class="mt-4 space-y-2.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Subtotal (<span x-text="selectedCount"></span> item)</dt>
                            <dd class="font-semibold text-ink-950" x-text="rupiah(selectedSubtotal)"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Ongkir</dt>
                            <dd class="text-xs text-ink-400">Dihitung saat checkout</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex items-end justify-between border-t border-paper-200 pt-4">
                        <span class="text-xs font-bold text-ink-500">Estimasi total</span>
                        <span class="text-xl font-extrabold text-ink-950" x-text="rupiah(selectedSubtotal)"></span>
                    </div>
                    <button type="submit" form="cart-checkout-form"
                            class="btn-primary mt-5 w-full disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="selected.length === 0">
                        Lanjut ke checkout <x-icon name="arrow-right" class="h-4 w-4" />
                    </button>
                    <p x-show="selected.length === 0" x-cloak class="mt-3 text-[11px] font-semibold text-rose-600">
                        Pilih minimal satu produk untuk melanjutkan.
                    </p>
                    <p class="mt-3 flex items-start gap-1.5 text-[11px] leading-relaxed text-ink-400">
                        <x-icon name="information-circle" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                        Pesanan dipecah per toko, tetapi dibayar dalam satu transaksi.
                    </p>
                </div>
            </aside>
        </div>
    @endif
</x-account-shell>
