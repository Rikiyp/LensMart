<x-account-shell title="Wishlist" subtitle="Produk yang Anda simpan untuk dibeli nanti.">
    @if ($items->isEmpty())
        <x-empty-state icon="heart" title="Wishlist masih kosong" description="Simpan produk favorit dengan menekan ikon hati di katalog atau halaman produk.">
            <a href="{{ route('products.index') }}" class="btn-primary btn-sm">Jelajahi katalog</a>
        </x-empty-state>
    @else
        <div class="mb-4 flex items-center justify-between">
            <p class="text-xs text-ink-500">{{ $items->total() }} produk tersimpan</p>
            <a href="{{ route('products.index') }}" class="text-xs font-bold text-amber-600 hover:text-amber-700">Tambah produk lain</a>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($items as $item)
                @php
                    $product = $item->product;
                @endphp
                @if ($product)
                    <article class="group flex flex-col overflow-hidden rounded-2xl border border-paper-300/80 bg-white transition hover:-translate-y-1 hover:shadow-soft">
                        <div class="relative">
                            <a href="{{ route('products.show', $product) }}" class="block aspect-[4/3] overflow-hidden bg-paper-100">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">
                            </a>
                            <form method="POST" action="{{ route('customer.wishlist.destroy', $product) }}" class="absolute right-3 top-3" x-confirm="{ title: 'Hapus dari wishlist?', message: 'Produk ini akan dihapus dari wishlist Anda.', confirm: 'Ya, hapus', tone: 'danger' }">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-rose-500 shadow-sm backdrop-blur transition hover:bg-rose-50" title="Hapus dari wishlist">
                                    <x-icon name="heart" class="h-4 w-4 fill-rose-500" />
                                </button>
                            </form>
                            <div class="absolute left-3 top-3">
                                <span class="badge {{ $product->condition->badge() }}">{{ $product->condition->label() }}</span>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">{{ $product->brand?->name }} · {{ $product->category?->name }}</p>
                            <a href="{{ route('products.show', $product) }}" class="mt-1 line-clamp-2 text-sm font-bold leading-snug text-ink-950 hover:text-amber-600">{{ $product->name }}</a>
                            <p class="mt-1 flex items-center gap-1 text-[11px] text-ink-500">
                                <x-icon name="store" class="h-3.5 w-3.5" /> {{ $product->store?->name }}
                            </p>

                            <div class="mt-3">
                                <x-star-rating :rating="$product->rating" :count="$product->rating_count" />
                            </div>

                            <div class="mt-auto pt-4">
                                <div class="flex items-end justify-between gap-2">
                                    <div>
                                        <p class="text-[10px] text-ink-400">Harga saat disimpan</p>
                                        <p class="text-lg font-extrabold tracking-tight text-ink-950">{{ format_rupiah($product->price) }}</p>
                                    </div>
                                    @if ($product->price < $item->price_when_added)
                                        <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20">
                                            <x-icon name="arrow-down-tray" class="h-3 w-3" /> Turun {{ format_rupiah($item->price_when_added - $product->price) }}
                                        </span>
                                    @elseif ($product->price > $item->price_when_added)
                                        <span class="badge bg-paper-100 text-ink-500 ring-ink-500/15">Naik {{ format_rupiah($product->price - $item->price_when_added) }}</span>
                                    @endif
                                </div>

                                @if ($product->stock > 0 && $product->isPubliclyVisible())
                                    <form method="POST" action="{{ route('customer.cart.store', $product) }}" class="mt-3">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn-primary w-full btn-sm">
                                            <x-icon name="shopping-cart" class="h-3.5 w-3.5" /> Tambah ke keranjang
                                        </button>
                                    </form>
                                @else
                                    <button type="button" disabled class="btn-outline mt-3 w-full btn-sm opacity-60">
                                        <x-icon name="exclamation-circle" class="h-3.5 w-3.5" /> Stok habis
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>

        <div class="mt-6">{{ $items->links() }}</div>
    @endif
</x-account-shell>
