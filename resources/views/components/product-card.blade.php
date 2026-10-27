@props(['product', 'wishlisted' => null])

@php
    $wishlisted = $wishlisted ?? in_array($product->id, $wishlistedProductIds ?? [], true);
    $inCompare = in_array($product->id, session('compare', []), true);
    $isOutOfStock = $product->stock <= 0 || $product->status === \App\Enums\ProductStatus::OutOfStock;
    $isUsed = $product->condition !== \App\Enums\ProductCondition::New;
    $revealClass = 'opacity-100 transition duration-200 sm:translate-y-1 sm:opacity-0 sm:group-hover:translate-y-0 sm:group-hover:opacity-100 sm:focus-within:translate-y-0 sm:focus-within:opacity-100';
@endphp

<article {{ $attributes->merge(['class' => 'group relative flex flex-col']) }}>
    <div class="relative overflow-hidden rounded-lg border border-paper-300 bg-white">
        <a href="{{ route('products.show', $product) }}" class="block">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                 class="aspect-[4/3] w-full object-cover transition duration-[600ms] ease-out group-hover:scale-[1.035]">
        </a>

        @if ($isUsed)
            <span class="pointer-events-none absolute left-2.5 top-2.5 inline-flex items-center gap-1.5 rounded bg-ink-950/85 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.08em] text-white backdrop-blur">
                <span class="h-1 w-1 rounded-full bg-amber-400"></span> {{ $product->condition->label() }}
            </span>
        @endif

        @if ($isOutOfStock)
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-paper-100/70 backdrop-blur-[1px]">
                <span class="rounded border border-ink-950/15 bg-white px-3 py-1.5 text-[10.5px] font-semibold uppercase tracking-[0.14em] text-ink-700">Stok habis</span>
            </div>
        @endif

        <div class="absolute right-2.5 top-2.5 z-10 flex flex-col gap-1.5">
            <div class="{{ $wishlisted ? '' : $revealClass }}">
                @if ($wishlisted)
                    <form method="POST" action="{{ route('customer.wishlist.destroy', $product) }}"
                          x-confirm="{ title: 'Hapus dari wishlist?', message: 'Produk ini akan dihapus dari wishlist Anda.', confirm: 'Ya, hapus', tone: 'danger' }">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-md border border-rose-200 bg-white/95 text-rose-600 shadow-sm backdrop-blur transition hover:bg-rose-50" title="Hapus dari wishlist" aria-label="Hapus {{ $product->name }} dari wishlist" aria-pressed="true">
                            <x-icon name="heart" class="h-[15px] w-[15px] fill-rose-600 text-rose-600" />
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('customer.wishlist.store', $product) }}">
                        @csrf
                        <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-md border border-paper-300 bg-white/95 text-ink-600 shadow-sm backdrop-blur transition hover:text-rose-600" title="Tambah ke wishlist" aria-label="Tambah {{ $product->name }} ke wishlist" aria-pressed="false">
                            <x-icon name="heart" class="h-[15px] w-[15px]" />
                        </button>
                    </form>
                @endif
            </div>
            <div class="{{ $revealClass }}">
                <form method="POST" action="{{ route('compare.store', $product) }}">
                    @csrf
                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-md border border-paper-300 bg-white/95 shadow-sm backdrop-blur transition hover:text-amber-700 {{ $inCompare ? 'text-amber-700' : 'text-ink-600' }}" title="Bandingkan">
                        <x-icon name="sliders" class="h-[15px] w-[15px]" />
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="flex flex-1 flex-col pt-3">
        <div class="flex items-center justify-between gap-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-ink-500">
            <span class="truncate">{{ $product->brand?->name }}</span>
            <span class="flex shrink-0 items-center gap-1 normal-case tracking-normal text-ink-400">
                @if ($product->store?->isVerified())
                    <x-icon name="shield-check" class="h-3 w-3 text-emerald-600" />
                @endif
                <span class="max-w-[110px] truncate">{{ $product->store?->name }}</span>
            </span>
        </div>

        <h3 class="mt-1.5 line-clamp-2 text-[13.5px] font-semibold leading-snug text-ink-950">
            <a href="{{ route('products.show', $product) }}" class="transition after:absolute after:inset-0 after:z-0 after:content-[''] hover:text-amber-700">{{ $product->name }}</a>
        </h3>

        <div class="mt-1.5 flex items-center gap-2">
            <x-star-rating :rating="$product->rating" :count="$product->rating_count" size="h-3 w-3" />
        </div>

        <div class="mt-auto flex items-end justify-between gap-2 pt-3">
            <p class="tabular text-[14.5px] font-bold tracking-tight text-ink-950">{{ format_rupiah($product->price) }}</p>
            <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-[0.1em] text-ink-400 transition group-hover:text-ink-950">
                Detail <x-icon name="arrow-right" class="h-3 w-3" />
            </span>
        </div>
    </div>
</article>
