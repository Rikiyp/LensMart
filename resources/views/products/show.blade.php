<x-layouts.public :title="$product->name" :description="$product->description ? str($product->description)->limit(155) : null">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <nav class="flex flex-wrap items-center gap-2 text-[10.5px] font-medium uppercase tracking-[0.12em] text-ink-400">
            <a href="{{ route('home') }}" class="transition hover:text-ink-950">Beranda</a>
            <span class="text-paper-400">/</span>
            <a href="{{ route('products.index') }}" class="transition hover:text-ink-950">Katalog</a>
            <span class="text-paper-400">/</span>
            <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="transition hover:text-ink-950">{{ $product->category->name }}</a>
            <span class="text-paper-400">/</span>
            <span class="max-w-[240px] truncate text-ink-700">{{ $product->name }}</span>
        </nav>

        <div class="mt-6 grid gap-8 lg:grid-cols-[1.05fr_.95fr]">
            {{-- Gallery --}}
            <div x-data="productGallery(@js($product->images->map(fn ($image) => $image->url)->values()))">
                <div class="overflow-hidden rounded-lg border border-paper-300 bg-white">
                    <img :src="current" alt="{{ $product->name }}" class="aspect-[4/3] w-full object-cover lg:aspect-square">
                </div>
                @if ($product->images->count() > 1)
                    <div class="mt-3 flex gap-2.5 overflow-x-auto pb-1 no-scrollbar">
                        <template x-for="(image, index) in images" :key="index">
                            <button type="button" @click="select(index)" class="h-[74px] w-[74px] shrink-0 overflow-hidden rounded-md border-2 transition"
                                    :class="active === index ? 'border-ink-950' : 'border-paper-300 opacity-70 hover:opacity-100'">
                                <img :src="image" alt="Foto produk" class="h-full w-full object-cover">
                            </button>
                        </template>
                    </div>
                @endif
            </div>

            {{-- Purchase panel --}}
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge {{ $product->condition->badge() }}">{{ $product->condition->label() }}</span>
                    <a href="{{ route('products.index', ['brand' => $product->brand_id]) }}" class="text-[10.5px] font-semibold uppercase tracking-[0.12em] text-ink-500 transition hover:text-ink-950">{{ $product->brand->name }}</a>
                    <span class="h-3 w-px bg-paper-400"></span>
                    <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="text-[10.5px] font-semibold uppercase tracking-[0.12em] text-ink-500 transition hover:text-ink-950">{{ $product->category->name }}</a>
                </div>

                <h1 class="mt-4 font-display text-[32px] font-semibold leading-[1.05] tracking-[-0.025em] text-ink-950 sm:text-[40px]">{{ $product->name }}</h1>
                <p class="mt-2 text-[12px] uppercase tracking-[0.14em] text-ink-500">{{ $product->model_name }}</p>

                <div class="mt-4 flex flex-wrap items-center gap-4">
                    <x-star-rating :rating="$ratingAverage" :count="$product->reviews_count" size="h-3.5 w-3.5" />
                    @if ($product->reviews_count > 0)
                        <a href="#ulasan" class="text-[11px] font-semibold text-ink-500 underline decoration-paper-400 underline-offset-4 hover:text-ink-950">Lihat ulasan</a>
                    @endif
                </div>

                <div class="mt-6 border-y border-paper-300 py-5">
                    <p class="field-label">Harga</p>
                    <p class="tabular mt-1.5 font-display text-[36px] font-semibold leading-none tracking-[-0.03em] text-ink-950">{{ format_rupiah($product->price) }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-[11.5px]">
                        @if ($product->stock > 0)
                            <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-700">
                                <x-icon name="check-circle" class="h-3.5 w-3.5" /> Stok tersedia ({{ $product->stock }})
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 font-semibold text-rose-700">
                                <x-icon name="exclamation-circle" class="h-3.5 w-3.5" /> Stok habis
                            </span>
                        @endif
                        <span class="text-paper-400">/</span>
                        <span class="text-ink-500">Dikirim dari {{ $product->store->city ?? 'Indonesia' }}</span>
                    </div>

                    @if (! auth()->check() || auth()->user()->isCustomer())
                        <form method="POST" action="{{ route('customer.cart.store', $product) }}" class="mt-5" x-data="{ qty: 1 }">
                            @csrf
                            <div class="flex flex-col gap-2.5 sm:flex-row">
                                <div class="flex h-11 items-center rounded-lg border border-paper-400 bg-white">
                                    <button type="button" @click="qty = Math.max(1, qty - 1)" class="flex h-11 w-10 items-center justify-center rounded-l-lg text-ink-500 hover:bg-paper-50" aria-label="Kurangi">
                                        <x-icon name="minus" class="h-4 w-4" />
                                    </button>
                                    <input type="number" name="quantity" x-model.number="qty" min="1" max="{{ max(1, $product->stock) }}"
                                           class="tabular h-11 w-11 border-0 bg-transparent p-0 text-center text-[13px] font-semibold text-ink-950 focus:ring-0">
                                    <button type="button" @click="qty = Math.min({{ max(1, $product->stock) }}, qty + 1)" class="flex h-11 w-10 items-center justify-center rounded-r-lg text-ink-500 hover:bg-paper-50" aria-label="Tambah">
                                        <x-icon name="plus" class="h-4 w-4" />
                                    </button>
                                </div>
                                <button type="submit" class="btn-primary h-11 flex-1" @disabled($product->stock <= 0)>
                                    <x-icon name="shopping-cart" class="h-4 w-4" /> Tambah ke keranjang
                                </button>
                            </div>
                        </form>

                        <div class="mt-2.5 grid grid-cols-2 gap-2.5">
                            @if ($inWishlist)
                                <form method="POST" action="{{ route('customer.wishlist.destroy', $product) }}" x-confirm="{ title: 'Hapus dari wishlist?', message: 'Produk ini akan dihapus dari wishlist Anda.', confirm: 'Ya, hapus', tone: 'danger' }">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline w-full !py-2.5 text-[12px] text-rose-700">
                                        <x-icon name="heart" class="h-4 w-4 fill-rose-600 text-rose-600" /> Tersimpan
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('customer.wishlist.store', $product) }}">
                                    @csrf
                                    <button type="submit" class="btn-outline w-full !py-2.5 text-[12px]">
                                        <x-icon name="heart" class="h-4 w-4" /> Wishlist
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('compare.store', $product) }}">
                                @csrf
                                <button type="submit" class="btn-outline w-full !py-2.5 text-[12px] {{ $inCompare ? 'border-ink-950 bg-ink-950 text-white hover:bg-ink-900' : '' }}">
                                    <x-icon name="sliders" class="h-4 w-4" /> {{ $inCompare ? 'Dibandingkan' : 'Bandingkan' }}
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="mt-5 rounded-md border border-paper-300 bg-paper-50 p-3 text-[12px] text-ink-500">Masuk sebagai pelanggan untuk membeli produk ini.</div>
                    @endif
                </div>

                {{-- Store --}}
                <div class="mt-5 flex items-center gap-3 border-b border-paper-300 pb-5">
                    <img src="{{ $product->store->logo_url }}" alt="{{ $product->store->name }}" class="h-11 w-11 rounded-md border border-paper-300 object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-1.5 text-[13px] font-semibold text-ink-950">
                            {{ $product->store->name }}
                            <x-icon name="shield-check" class="h-3.5 w-3.5 text-emerald-600" />
                        </p>
                        <p class="mt-0.5 flex items-center gap-1 text-[11px] text-ink-500">
                            <x-icon name="map-pin" class="h-3 w-3" /> {{ $product->store->city ?? 'Indonesia' }}
                        </p>
                    </div>
                    <a href="{{ route('products.index', ['q' => $product->store->name]) }}" class="text-[10.5px] font-semibold uppercase tracking-[0.12em] text-ink-500 transition hover:text-ink-950">Lihat produk</a>
                </div>

                <div class="mt-4 grid gap-2 text-[11px] text-ink-500 sm:grid-cols-3">
                    <p class="flex items-center gap-1.5"><x-icon name="shield-check" class="h-3.5 w-3.5 text-emerald-600" /> Toko terverifikasi</p>
                    <p class="flex items-center gap-1.5"><x-icon name="truck" class="h-3.5 w-3.5 text-ink-400" /> Kirim seluruh Indonesia</p>
                    <p class="flex items-center gap-1.5"><x-icon name="banknotes" class="h-3.5 w-3.5 text-ink-400" /> Pembayaran aman</p>
                </div>
            </div>
        </div>

        {{-- Deskripsi & spesifikasi --}}
        <div class="mt-14 grid gap-8 lg:grid-cols-[1.1fr_.9fr] lg:gap-12">
            <section class="border-t border-ink-950 pt-5">
                <h2 class="text-[10.5px] font-semibold uppercase tracking-[0.18em] text-ink-500">Deskripsi produk</h2>
                <div class="mt-4 text-[13.5px] leading-7 text-ink-600">
                    @if ($product->description)
                        {!! nl2br(e($product->description)) !!}
                    @else
                        <p class="text-ink-400">Belum ada deskripsi untuk produk ini.</p>
                    @endif
                </div>

                @if ($product->compatibilities->isNotEmpty())
                    <div class="mt-6 border-t border-paper-300 pt-5">
                        <h3 class="text-[10.5px] font-semibold uppercase tracking-[0.18em] text-ink-500">Kompatibilitas</h3>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($product->compatibilities as $compatibility)
                                <span class="rounded border border-paper-300 bg-white px-2 py-1 text-[11px] text-ink-600">{{ $compatibility->brand->name }} {{ $compatibility->model_name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($otherOffers->isNotEmpty())
                    <div class="mt-6 border-t border-paper-300 pt-5">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-[10.5px] font-semibold uppercase tracking-[0.18em] text-ink-500">Tersedia di toko lain</h3>
                            <span class="text-[10px] font-medium uppercase tracking-[0.12em] text-ink-400">{{ $otherOffers->count() }} penawaran</span>
                        </div>
                        <div class="mt-3 divide-y divide-paper-200 overflow-hidden rounded-lg border border-paper-300 bg-white">
                            @foreach ($otherOffers as $offer)
                                <a href="{{ route('products.show', $offer) }}" class="flex items-center gap-3 p-3 transition hover:bg-paper-50">
                                    <img src="{{ $offer->image_url }}" alt="{{ $offer->name }}" class="h-11 w-11 shrink-0 rounded-md border border-paper-200 object-cover">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-[12px] font-semibold text-ink-950">{{ $offer->store->name }}</p>
                                        <p class="mt-0.5 text-[10.5px] text-ink-500">{{ $offer->condition->label() }} · Stok {{ $offer->stock }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="tabular text-[12.5px] font-bold text-ink-950">{{ format_rupiah($offer->price) }}</p>
                                        @if ($offer->price < $product->price)
                                            <p class="mt-0.5 text-[10px] font-semibold text-emerald-700">Hemat {{ format_rupiah($product->price - $offer->price) }}</p>
                                        @endif
                                    </div>
                                    <x-icon name="chevron-right" class="h-3.5 w-3.5 shrink-0 text-paper-400" />
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            <section class="border-t border-ink-950 pt-5">
                <h2 class="text-[10.5px] font-semibold uppercase tracking-[0.18em] text-ink-500">Spesifikasi teknis</h2>
                <dl class="mt-4 divide-y divide-paper-200 text-[12.5px]">
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-ink-500">Merek</dt>
                        <dd class="font-semibold text-ink-950">{{ $product->brand->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-ink-500">Model</dt>
                        <dd class="text-right font-semibold text-ink-950">{{ $product->model_name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-ink-500">Kondisi</dt>
                        <dd class="font-semibold text-ink-950">{{ $product->condition->label() }}</dd>
                    </div>
                    @foreach ($product->specs as $spec)
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-ink-500">{{ $spec->specAttribute->name }}{{ $spec->specAttribute->unit ? ' ('.$spec->specAttribute->unit.')' : '' }}</dt>
                            <dd class="text-right font-semibold text-ink-950">{{ $spec->value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>

        {{-- Reviews --}}
        <section id="ulasan" class="mt-14 scroll-mt-24 border-t border-paper-300 pt-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="section-eyebrow">Ulasan pembeli</p>
                    <h2 class="mt-3 font-display text-[26px] font-semibold leading-tight tracking-[-0.02em] text-ink-950">Apa kata mereka</h2>
                </div>
                @if ($reviewableOrder)
                    <a href="{{ route('customer.reviews.create', [$reviewableOrder, $product]) }}" class="btn-primary btn-sm">
                        <x-icon name="star" class="h-3.5 w-3.5" /> Beri ulasan
                    </a>
                @endif
            </div>

            <div class="mt-6 grid gap-10 lg:grid-cols-[260px_1fr]">
                <div class="h-fit border-t border-ink-950 pt-4">
                    <p class="tabular font-display text-[46px] font-semibold leading-none tracking-[-0.04em] text-ink-950">{{ number_format($ratingAverage, 1, ',', '.') }}</p>
                    <x-star-rating :rating="$ratingAverage" size="h-3.5 w-3.5" class="mt-2.5" />
                    <p class="mt-1.5 text-[11px] text-ink-500">Dari {{ $product->reviews_count }} ulasan</p>
                    <div class="mt-5 space-y-2">
                        @foreach ([5, 4, 3, 2, 1] as $star)
                            @php
                                $count = $product->reviews->where('rating', $star)->count();
                                $percent = $product->reviews_count > 0 ? ($count / $product->reviews_count) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-2 text-[11px]">
                                <span class="tabular w-2.5 font-semibold text-ink-500">{{ $star }}</span>
                                <x-icon name="star" class="h-2.5 w-2.5 text-amber-500" />
                                <div class="h-1 flex-1 overflow-hidden rounded-sm bg-paper-300">
                                    <div class="h-full rounded-sm bg-ink-950" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="tabular w-5 text-right text-ink-400">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="divide-y divide-paper-200">
                    @forelse ($product->reviews as $review)
                        <article class="py-6 first:pt-0">
                            <div class="flex items-start gap-3">
                                <img src="{{ $review->customer->avatar_url }}" alt="{{ $review->customer->name }}" class="h-9 w-9 rounded-full object-cover">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <p class="text-[12.5px] font-semibold text-ink-950">{{ $review->customer->name }}</p>
                                            <p class="mt-0.5 text-[10.5px] uppercase tracking-[0.1em] text-ink-400">{{ format_tanggal($review->created_at) }}</p>
                                        </div>
                                        <x-star-rating :rating="$review->rating" size="h-3 w-3" />
                                    </div>
                                    @if ($review->comment)
                                        <p class="mt-3 text-[13px] leading-6 text-ink-600">{{ $review->comment }}</p>
                                    @endif
                                    @if ($review->images->isNotEmpty())
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($review->images as $image)
                                                <a href="{{ $image->url }}" target="_blank" class="h-16 w-16 overflow-hidden rounded-md border border-paper-300">
                                                    <img src="{{ $image->url }}" alt="Foto ulasan" class="h-full w-full object-cover">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($review->store_reply)
                                        <div class="mt-4 border-l-2 border-amber-400 bg-paper-50 py-2.5 pl-3.5">
                                            <p class="flex items-center gap-1.5 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-ink-600">
                                                <x-icon name="store" class="h-3 w-3" /> Balasan {{ $product->store->name }}
                                            </p>
                                            <p class="mt-1 text-[12px] leading-6 text-ink-600">{{ $review->store_reply }}</p>
                                        </div>
                                    @endif
                                    @if (auth()->id() === $review->customer_id)
                                        <a href="{{ route('customer.reviews.edit', $review) }}" class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-ink-600 underline decoration-amber-500/50 underline-offset-4 hover:text-ink-950">
                                            <x-icon name="pencil" class="h-3 w-3" /> Edit ulasan
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <x-empty-state icon="chat" title="Belum ada ulasan" description="Jadilah yang pertama mengulas produk ini setelah pesanan Anda selesai." />
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Related --}}
        @if ($relatedProducts->isNotEmpty())
            <section class="mt-14">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="section-eyebrow">Masih di kategori {{ $product->category->name }}</p>
                        <h2 class="mt-3 font-display text-[26px] font-semibold leading-tight tracking-[-0.02em] text-ink-950">Produk serupa</h2>
                    </div>
                </div>
                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.public>
