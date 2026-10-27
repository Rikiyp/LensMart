<x-layouts.public title="Katalog Produk">
    @php
        $filterKeys = ['q', 'category', 'brand', 'condition', 'min_price', 'max_price', 'rating'];
        $activeFilters = collect($filterKeys)->filter(fn ($key) => filled($filters[$key] ?? null));
        $hasPanelFilters = $activeFilters->reject(fn ($key) => in_array($key, ['category', 'q'], true))->isNotEmpty();
        $panelFilterCount = $activeFilters->reject(fn ($key) => $key === 'category')->count();
        $filterLabels = [
            'q' => 'Pencarian',
            'brand' => 'Merek',
            'condition' => 'Kondisi',
            'min_price' => 'Harga min',
            'max_price' => 'Harga maks',
            'rating' => 'Rating',
        ];

        $chipValues = [];
        foreach ($activeFilters as $key) {
            $chipValues[$key] = match ($key) {
                'condition' => \App\Enums\ProductCondition::tryFrom((string) $filters[$key])?->label() ?? $filters[$key],
                'brand' => $brands->firstWhere('id', (int) $filters[$key])?->name ?? $filters[$key],
                'rating' => '≥ '.$filters[$key],
                'min_price', 'max_price' => format_rupiah((int) $filters[$key]),
                default => (string) $filters[$key],
            };
        }
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8" x-data="{ open: {{ $hasPanelFilters ? 'true' : 'false' }} }">
        <nav class="flex items-center gap-2 text-[10.5px] font-medium uppercase tracking-[0.12em] text-ink-400">
            <a href="{{ route('home') }}" class="transition hover:text-ink-950">Beranda</a>
            <span class="text-paper-400">/</span>
            <span class="text-ink-700">Katalog</span>
        </nav>

        <div class="mt-6 flex flex-wrap items-end justify-between gap-4 border-b border-paper-300 pb-6">
            <div>
                <p class="section-eyebrow">Katalog</p>
                <h1 class="mt-3 font-display text-[38px] font-semibold leading-none tracking-[-0.03em] text-ink-950 sm:text-[46px]">
                    @if (! empty($filters['q']))
                        “{{ $filters['q'] }}”
                    @else
                        Semua gear
                    @endif
                </h1>
            </div>
            <p class="text-[12.5px] text-ink-500">
                <span class="tabular font-semibold text-ink-950">{{ $products->total() }}</span> produk dari toko terverifikasi
            </p>
        </div>

        {{-- Category chips --}}
        <div class="mt-5 flex gap-1.5 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('products.index', collect(request()->query())->except(['category', 'page'])->all()) }}"
               class="shrink-0 rounded-md border px-3 py-1.5 text-[12px] font-medium transition {{ empty($filters['category']) ? 'border-ink-950 bg-ink-950 text-white' : 'border-paper-400 bg-white text-ink-600 hover:border-ink-950/30 hover:text-ink-950' }}">
                Semua
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('products.index', array_merge(collect(request()->query())->except(['category', 'page'])->all(), ['category' => $category->id])) }}"
                   class="shrink-0 rounded-md border px-3 py-1.5 text-[12px] font-medium transition {{ (string) ($filters['category'] ?? '') === (string) $category->id ? 'border-ink-950 bg-ink-950 text-white' : 'border-paper-400 bg-white text-ink-600 hover:border-ink-950/30 hover:text-ink-950' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        {{-- Toolbar --}}
        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-y border-paper-300 py-2.5">
            <div class="flex items-center gap-2">
                <button type="button" @click="open = ! open"
                        class="inline-flex items-center gap-2 rounded-md border px-3 py-1.5 text-[12px] font-semibold transition {{ $hasPanelFilters ? 'border-ink-950 bg-ink-950 text-white' : 'border-paper-400 bg-white text-ink-800 hover:border-ink-950/30' }}">
                    <x-icon name="sliders" class="h-3.5 w-3.5" /> Filter
                    @if ($panelFilterCount)
                        <span class="flex h-4 min-w-4 items-center justify-center rounded bg-amber-400 px-1 text-[9.5px] font-bold text-ink-950">{{ $panelFilterCount }}</span>
                    @endif
                    <span class="transition" :class="open ? 'rotate-180' : ''"><x-icon name="chevron-down" class="h-3 w-3" /></span>
                </button>

                @if ($activeFilters->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach ($activeFilters as $key)
                            @if ($key === 'category')
                                <a href="{{ route('products.index', collect(request()->query())->except(['category', 'page'])->all()) }}"
                                   class="inline-flex items-center gap-1.5 rounded border border-paper-400 bg-white px-2 py-1 text-[11px] font-medium text-ink-700 hover:border-ink-950/30">
                                    {{ $categories->firstWhere('id', (int) $filters['category'])?->name ?? 'Kategori' }}
                                    <x-icon name="x-mark" class="h-3 w-3 text-ink-400" />
                                </a>
                            @else
                                <a href="{{ route('products.index', collect(request()->query())->except([$key, 'page'])->all()) }}"
                                   class="inline-flex items-center gap-1.5 rounded border border-paper-400 bg-white px-2 py-1 text-[11px] font-medium text-ink-700 hover:border-ink-950/30">
                                    {{ $filterLabels[$key] }}@if ($key !== 'q'): <span class="font-semibold text-ink-950">{{ $chipValues[$key] }}</span>@endif
                                    <x-icon name="x-mark" class="h-3 w-3 text-ink-400" />
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2">
                @foreach ($filterKeys as $key)
                    @if ($key !== 'sort' && ! empty($filters[$key]))
                        <input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">
                    @endif
                @endforeach
                <label for="sort" class="hidden text-[10px] font-semibold uppercase tracking-[0.14em] text-ink-400 sm:block">Urutkan</label>
                <select id="sort" name="sort" onchange="this.form.submit()" class="select w-auto min-w-[152px] !py-1.5 !text-[12px]">
                    <option value="terbaru" @selected(($filters['sort'] ?? 'terbaru') === 'terbaru')>Terbaru</option>
                    <option value="termurah" @selected(($filters['sort'] ?? '') === 'termurah')>Harga terendah</option>
                    <option value="termahal" @selected(($filters['sort'] ?? '') === 'termahal')>Harga tertinggi</option>
                    <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Rating tertinggi</option>
                </select>
            </form>
        </div>

        {{-- Filter panel --}}
        <form method="GET" action="{{ route('products.index') }}" x-show="open" x-cloak x-transition.opacity.duration.150ms
              class="mt-3 rounded-lg border border-paper-300 bg-white p-4">
            @if (! empty($filters['sort']))
                <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            @endif
            @if (! empty($filters['category']))
                <input type="hidden" name="category" value="{{ $filters['category'] }}">
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="filter-q" class="field-label">Pencarian</label>
                    <input id="filter-q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kamera, lensa, merek…" class="input mt-2 !py-2 text-[12px]">
                </div>

                <div>
                    <label for="filter-brand" class="field-label">Merek</label>
                    <select id="filter-brand" name="brand" class="select mt-2 !py-2 text-[12px]">
                        <option value="">Semua merek</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((string) ($filters['brand'] ?? '') === (string) $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-condition" class="field-label">Kondisi</label>
                    <select id="filter-condition" name="condition" class="select mt-2 !py-2 text-[12px]">
                        <option value="">Semua kondisi</option>
                        @foreach ($conditions as $condition)
                            <option value="{{ $condition->value }}" @selected(($filters['condition'] ?? '') === $condition->value)>{{ $condition->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <p class="field-label">Rentang harga</p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" placeholder="Min" min="0" class="input !py-2 text-[12px]">
                        <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" placeholder="Maks" min="0" class="input !py-2 text-[12px]">
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-paper-200 pt-4">
                <div class="flex items-center gap-2.5">
                    <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-ink-400">Rating</span>
                    <div class="flex items-center gap-1.5">
                        @foreach ([4, 3, 2, 1] as $rating)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $rating }}" class="peer sr-only" @checked((string) ($filters['rating'] ?? '') === (string) $rating)>
                                <span class="flex h-7 items-center gap-0.5 rounded border border-paper-400 px-2 text-[11px] font-semibold text-ink-500 transition peer-checked:border-ink-950 peer-checked:bg-ink-950 peer-checked:text-white">
                                    {{ $rating }}<x-icon name="star" class="h-2.5 w-2.5" />
                                </span>
                            </label>
                        @endforeach
                        <label class="ml-1 flex cursor-pointer items-center gap-1.5 text-[11px] text-ink-500">
                            <input type="radio" name="rating" value="" class="h-3.5 w-3.5 border-paper-400 text-ink-950 focus:ring-ink-950" @checked(empty($filters['rating']))>
                            Semua
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('products.index') }}" class="text-[11.5px] font-semibold text-ink-500 underline decoration-paper-400 underline-offset-4 hover:text-ink-950">Reset</a>
                    <button type="submit" class="btn-primary btn-sm">Terapkan filter</button>
                </div>
            </div>
        </form>

        {{-- Grid --}}
        <div class="mt-8">
            @if ($products->isEmpty())
                <x-empty-state icon="search" title="Produk tidak ditemukan" description="Coba ubah kata kunci atau reset filter untuk melihat semua produk.">
                    <a href="{{ route('products.index') }}" class="btn-primary btn-sm">Reset filter</a>
                </x-empty-state>
            @else
                <div class="grid gap-x-5 gap-y-9 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-12 border-t border-paper-300 pt-6">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.public>
