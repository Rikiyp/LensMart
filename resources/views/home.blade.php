<x-layouts.public>
    @php
        $hero = $latestProducts->first();
        $heroAlt = $latestProducts->skip(1)->first();
    @endphp

    {{-- Hero --}}
    <section class="border-b border-paper-300 bg-paper-100">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 py-14 lg:grid-cols-[1.02fr_0.98fr] lg:gap-16 lg:py-20">
                <div>
                    <p class="flex items-center gap-3 text-[10.5px] font-semibold uppercase tracking-[0.22em] text-ink-500">
                        <span class="h-px w-8 bg-amber-500"></span> Katalog pilihan — {{ now()->year }}
                    </p>

                    <h1 class="mt-6 font-display text-[42px] font-semibold leading-[0.94] tracking-[-0.035em] text-ink-950 sm:text-[58px] lg:text-[68px]">
                        Gear yang layak<br class="hidden sm:block">
                        untuk <em class="italic text-amber-600">setiap frame.</em>
                    </h1>

                    <p class="mt-6 max-w-md text-[14px] leading-7 text-ink-600">
                        Dari DSLR pertama hingga lensa profesional. Bandingkan spesifikasi, cek harga antar toko, dan belanja dari partner terverifikasi di seluruh Indonesia.
                    </p>

                    <div class="mt-8 flex flex-col gap-2.5 sm:flex-row">
                        <a href="{{ route('products.index') }}" class="btn-primary px-6 py-3">
                            Jelajahi katalog <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                        <a href="{{ route('compare.index') }}" class="btn-outline px-6 py-3">
                            <x-icon name="sliders" class="h-4 w-4" /> Bandingkan produk
                        </a>
                    </div>

                    <dl class="mt-12 grid max-w-lg grid-cols-2 gap-y-6 border-t border-paper-400 pt-6 sm:grid-cols-4 sm:divide-x sm:divide-paper-400">
                        @foreach ([
                            ['value' => $productCount, 'suffix' => '+', 'label' => 'Produk'],
                            ['value' => $storeCount, 'suffix' => '', 'label' => 'Toko'],
                            ['value' => $orderCount, 'suffix' => '', 'label' => 'Pesanan'],
                            ['value' => $customerCount, 'suffix' => '', 'label' => 'Pelanggan'],
                        ] as $stat)
                            <div class="sm:px-4 sm:first:pl-0 sm:last:pr-0">
                                <dd class="tabular font-display text-[26px] font-semibold leading-none tracking-tight text-ink-950">
                                    {{ $stat['value'] }}<span class="text-amber-600">{{ $stat['suffix'] }}</span>
                                </dd>
                                <dt class="mt-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-500">{{ $stat['label'] }}</dt>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="lg:pl-2">
                    @if ($hero)
                        <figure>
                            <div class="mb-2.5 flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-500">
                                <span>Plate 01 — {{ $hero->category?->name }}</span>
                                <span class="text-ink-400">{{ $hero->brand?->name }}</span>
                            </div>

                            <a href="{{ route('products.show', $hero) }}" class="group block overflow-hidden rounded-lg border border-paper-300 bg-white">
                                <img src="{{ $hero->image_url }}" alt="{{ $hero->name }}"
                                     class="aspect-[16/11] w-full object-cover transition duration-700 ease-out group-hover:scale-[1.02]">
                            </a>

                            <figcaption class="mt-4 flex items-end justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate text-[15px] font-semibold tracking-tight text-ink-950">{{ $hero->name }}</p>
                                    <p class="mt-0.5 text-[11px] uppercase tracking-[0.14em] text-ink-500">{{ $hero->store?->city ?? 'Indonesia' }} · {{ $hero->condition->label() }}</p>
                                </div>
                                <p class="tabular shrink-0 text-[15px] font-bold tracking-tight text-ink-950">{{ format_rupiah($hero->price) }}</p>
                            </figcaption>
                        </figure>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @if ($heroAlt)
                                <a href="{{ route('products.show', $heroAlt) }}" class="group flex items-center gap-3 rounded-lg border border-paper-300 bg-white p-2.5 transition hover:border-ink-950/25">
                                    <img src="{{ $heroAlt->image_url }}" alt="{{ $heroAlt->name }}" class="h-14 w-14 shrink-0 rounded-md border border-paper-200 object-cover">
                                    <div class="min-w-0">
                                        <p class="truncate text-[11.5px] font-semibold text-ink-900">{{ $heroAlt->name }}</p>
                                        <p class="tabular mt-1 text-[11px] text-ink-500">{{ format_rupiah($heroAlt->price) }}</p>
                                    </div>
                                </a>
                            @endif

                            <div class="rounded-lg border border-paper-300 bg-white p-3">
                                <p class="text-[9.5px] font-semibold uppercase tracking-[0.18em] text-ink-400">Dijual oleh</p>
                                <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] font-semibold text-ink-900">
                                    {{ $hero->store?->name }}
                                    <x-icon name="shield-check" class="h-3.5 w-3.5 text-emerald-600" />
                                </p>
                                <p class="mt-1 text-[10.5px] text-ink-500">{{ $hero->store?->city ?? 'Indonesia' }} · Stok {{ $hero->stock }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex aspect-[16/11] items-center justify-center rounded-lg border border-paper-300 bg-white">
                            <x-icon name="camera" class="h-16 w-16 text-paper-400" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Brand rail --}}
    <div class="border-b border-paper-300 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-3.5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2.5 xl:flex-row xl:items-center xl:gap-7">
                <div class="flex items-center justify-between gap-4 xl:block xl:shrink-0">
                    <p class="text-[9.5px] font-semibold uppercase tracking-[0.2em] text-ink-400">Merek tersedia</p>
                    <a href="{{ route('products.index') }}" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-ink-500 transition hover:text-ink-950 xl:hidden">
                        Semua merek <x-icon name="arrow-right" class="h-3 w-3" />
                    </a>
                </div>

                <div class="brand-marquee no-scrollbar min-w-0 flex-1">
                    <div class="brand-marquee-track">
                        @foreach ([false, true] as $isClone)
                            <div class="brand-marquee-group" @if ($isClone) aria-hidden="true" @endif>
                                @foreach ($featuredBrands as $brand)
                                    <a href="{{ route('products.index', ['brand' => $brand->id]) }}"
                                       class="group flex h-8 shrink-0 items-center rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-4"
                                       title="Lihat produk {{ $brand->name }}"
                                       @if ($isClone) tabindex="-1" @endif>
                                        <x-brand-wordmark :slug="$brand->slug" :name="$brand->name" />
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('products.index') }}" class="hidden shrink-0 items-center gap-1 text-[10.5px] font-semibold uppercase tracking-[0.14em] text-ink-500 transition hover:text-ink-950 xl:flex">
                    Semua merek <x-icon name="arrow-right" class="h-3 w-3" />
                </a>
            </div>
        </div>
    </div>

    {{-- Service strip --}}
    <section class="bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:grid-cols-3 sm:px-6 lg:gap-12 lg:px-8">
            @foreach ([
                ['title' => 'Toko terverifikasi', 'desc' => 'Setiap partner melewati verifikasi dokumen dan profil toko sebelum berjualan.'],
                ['title' => 'Bandingkan 3 produk', 'desc' => 'Lihat spesifikasi berdampingan sebelum memutuskan pembelian.'],
                ['title' => 'Pembayaran transparan', 'desc' => 'Alur pembayaran simulasi dengan status pesanan yang jelas di setiap tahap.'],
            ] as $index => $highlight)
                <div class="border-t border-ink-950 pt-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-ink-400">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</p>
                    <p class="mt-2 font-display text-[17px] font-semibold tracking-tight text-ink-950">{{ $highlight['title'] }}</p>
                    <p class="mt-1.5 text-[12.5px] leading-6 text-ink-500">{{ $highlight['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Categories --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="flex flex-wrap items-end justify-between gap-4 border-b border-paper-300 pb-6">
            <div>
                <p class="section-eyebrow">01 — Telusuri</p>
                <h2 class="section-title">Mulai dari kebutuhan Anda</h2>
            </div>
            <a href="{{ route('products.index') }}" class="group hidden items-center gap-1.5 text-[11.5px] font-semibold uppercase tracking-[0.14em] text-ink-600 transition hover:text-ink-950 sm:flex">
                Semua kategori <x-icon name="arrow-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
            </a>
        </div>

        <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->id]) }}"
                   class="group relative flex items-start gap-4 rounded-lg border border-paper-300 bg-paper-50 p-4 transition duration-200 hover:border-ink-950/20 hover:bg-white">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-ink-950 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                        <x-category-icon :slug="$category->slug" class="h-[26px] w-[26px]" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[13px] font-semibold text-ink-950">{{ $category->name }}</span>
                        <span class="mt-0.5 block text-[10.5px] uppercase tracking-[0.1em] text-ink-400">{{ $category->public_products_count }} produk</span>
                    </span>
                    <x-icon name="arrow-right" class="mt-3 h-3.5 w-3.5 shrink-0 text-paper-400 transition group-hover:text-ink-950" />
                </a>
            @endforeach

            <a href="{{ route('products.index') }}"
               class="group flex items-center justify-between gap-4 rounded-lg border border-dashed border-paper-400 p-4 transition hover:border-ink-950/40 hover:bg-white">
                <span class="text-[12.5px] font-semibold text-ink-600 transition group-hover:text-ink-950">Lihat semua kategori</span>
                <x-icon name="arrow-top-right" class="h-4 w-4 text-ink-400 transition group-hover:text-ink-950" />
            </a>
        </div>
    </section>

    {{-- Latest products --}}
    <section class="border-y border-paper-300 bg-white py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-paper-300 pb-6">
                <div>
                    <p class="section-eyebrow">02 — Baru masuk</p>
                    <h2 class="section-title">Produk terbaru</h2>
                </div>
                <a href="{{ route('products.index', ['sort' => 'terbaru']) }}" class="group flex items-center gap-1.5 text-[11.5px] font-semibold uppercase tracking-[0.14em] text-ink-600 transition hover:text-ink-950">
                    Lihat semua <x-icon name="arrow-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
                </a>
            </div>

            <div class="mt-8 grid gap-x-5 gap-y-9 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($latestProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Editor picks --}}
    @if ($topProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-paper-300 pb-6">
                <div>
                    <p class="section-eyebrow">03 — Rating tertinggi</p>
                    <h2 class="section-title">Pilihan komunitas</h2>
                </div>
                <a href="{{ route('products.index', ['sort' => 'rating']) }}" class="group flex items-center gap-1.5 text-[11.5px] font-semibold uppercase tracking-[0.14em] text-ink-600 transition hover:text-ink-950">
                    Produk terbaik <x-icon name="arrow-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
                </a>
            </div>

            <div class="mt-8 grid gap-x-5 gap-y-9 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($topProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Partner CTA --}}
    <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-lg bg-ink-950 px-6 py-12 text-white sm:px-10 lg:px-14 lg:py-14">
            <div class="grid gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:gap-16">
                <div>
                    <p class="flex items-center gap-3 text-[10.5px] font-semibold uppercase tracking-[0.22em] text-amber-400">
                        <span class="h-px w-8 bg-amber-400/70"></span> Untuk pemilik toko
                    </p>
                    <h2 class="mt-5 max-w-xl font-display text-[30px] font-semibold leading-[1.08] tracking-[-0.02em] sm:text-[38px]">
                        Punya toko kamera? Jangkau fotografer di seluruh Indonesia.
                    </h2>
                    <p class="mt-4 max-w-lg text-[13px] leading-6 text-white/60">
                        Kelola katalog, proses pesanan, pantau ulasan, dan lihat laporan penjualan dalam satu dashboard. Verifikasi toko hanya butuh beberapa menit.
                    </p>
                    <div class="mt-8 flex flex-col gap-2.5 sm:flex-row">
                        <a href="{{ route('partner.register') }}" class="btn-accent px-6 py-3">Buka toko sekarang</a>
                        <a href="{{ route('login') }}" class="btn border border-white/20 px-6 py-3 text-white hover:border-white/40 hover:bg-white/5">Masuk sebagai partner</a>
                    </div>
                </div>

                <ul class="space-y-4 border-t border-white/10 pt-6 lg:border-l lg:border-t-0 lg:pl-10 lg:pt-0">
                    @foreach ([
                        'Katalog & stok terpusat',
                        'Pesanan dan pengiriman terlacak',
                        'Laporan penjualan per periode',
                    ] as $index => $item)
                        <li class="flex items-baseline gap-4 border-b border-white/10 pb-4 last:border-0">
                            <span class="tabular text-[10px] font-semibold text-amber-400/80">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-[13px] font-medium text-white/80">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
</x-layouts.public>
