<x-layouts.public title="Bandingkan Produk">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-xs text-ink-400">
            <a href="{{ route('home') }}" class="hover:text-ink-950">Beranda</a>
            <x-icon name="chevron-right" class="h-3 w-3" />
            <span class="font-semibold text-ink-700">Bandingkan</span>
        </nav>

        <div class="mt-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="section-eyebrow">Komparasi</p>
                <h1 class="mt-2 font-display text-4xl font-semibold tracking-tight text-ink-950">Bandingkan produk</h1>
                <p class="mt-2 text-sm text-ink-500">Pilih 2–3 produk untuk melihat spesifikasi berdampingan. Maksimal 3 produk.</p>
            </div>
            @if ($products->isNotEmpty())
                <form method="POST" action="{{ route('compare.clear') }}" x-confirm="{ title: 'Kosongkan perbandingan?', message: 'Semua produk akan dihapus dari daftar perbandingan.', confirm: 'Ya, kosongkan', tone: 'danger' }">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-outline btn-sm"><x-icon name="trash" class="h-3.5 w-3.5" /> Kosongkan</button>
                </form>
            @endif
        </div>

        @if ($products->count() < 2)
            <div class="mt-8">
                <x-empty-state icon="sliders" title="Pilih minimal 2 produk" description="Tambahkan produk dari halaman katalog atau detail produk untuk mulai membandingkan.">
                    <a href="{{ route('products.index') }}" class="btn-primary btn-sm">Jelajahi katalog</a>
                </x-empty-state>
            </div>

            @if ($products->count() === 1)
                <div class="mt-6">
                    <p class="mb-3 text-xs font-bold uppercase tracking-wider text-ink-500">Baru dipilih</p>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <x-product-card :product="$products->first()" />
                    </div>
                </div>
            @endif
        @else
            <div class="mt-8 overflow-x-auto rounded-3xl border border-paper-300 bg-white shadow-card">
                <table class="w-full min-w-[720px] border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-paper-300 bg-paper-50/60">
                            <th class="sticky left-0 z-10 w-40 bg-paper-50/95 p-4 text-left text-[11px] font-bold uppercase tracking-wider text-ink-500">Produk</th>
                            @foreach ($products as $product)
                                <th class="min-w-[220px] p-4 text-left align-top">
                                    <div class="relative">
                                        <form method="POST" action="{{ route('compare.destroy', $product) }}" class="absolute right-0 top-0" x-confirm="{ title: 'Hapus dari perbandingan?', message: 'Produk ini akan dihapus dari daftar perbandingan.', confirm: 'Ya, hapus', tone: 'danger' }">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-ink-400 shadow-sm ring-1 ring-paper-300 transition hover:text-rose-500" title="Hapus dari perbandingan">
                                                <x-icon name="x-mark" class="h-3.5 w-3.5" />
                                            </button>
                                        </form>
                                        <a href="{{ route('products.show', $product) }}" class="block pr-6">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-32 w-full rounded-xl object-cover">
                                            <p class="mt-3 line-clamp-2 text-sm font-bold leading-snug text-ink-950">{{ $product->name }}</p>
                                        </a>
                                        <p class="mt-1 text-[11px] text-ink-500">{{ $product->store->name }}</p>
                                        <p class="mt-2 text-lg font-extrabold text-ink-950">{{ format_rupiah($product->price) }}</p>
                                        <a href="{{ route('products.show', $product) }}" class="btn-primary btn-sm mt-3 w-full">Lihat detail</a>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-paper-200">
                        <tr>
                            <td class="sticky left-0 z-10 bg-white p-4 text-xs font-bold uppercase tracking-wider text-ink-500">Rating</td>
                            @foreach ($products as $product)
                                <td class="p-4"><x-star-rating :rating="$product->rating" :count="$product->rating_count" /></td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="sticky left-0 z-10 bg-white p-4 text-xs font-bold uppercase tracking-wider text-ink-500">Merek</td>
                            @foreach ($products as $product)
                                <td class="p-4 font-semibold text-ink-950">{{ $product->brand->name }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="sticky left-0 z-10 bg-white p-4 text-xs font-bold uppercase tracking-wider text-ink-500">Model</td>
                            @foreach ($products as $product)
                                <td class="p-4 text-ink-700">{{ $product->model_name }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="sticky left-0 z-10 bg-white p-4 text-xs font-bold uppercase tracking-wider text-ink-500">Kondisi</td>
                            @foreach ($products as $product)
                                <td class="p-4"><span class="badge {{ $product->condition->badge() }}">{{ $product->condition->label() }}</span></td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="sticky left-0 z-10 bg-white p-4 text-xs font-bold uppercase tracking-wider text-ink-500">Toko</td>
                            @foreach ($products as $product)
                                <td class="p-4">
                                    <p class="text-ink-700">{{ $product->store->name }}</p>
                                    <p class="text-[11px] text-ink-400">{{ $product->store->city }}</p>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="sticky left-0 z-10 bg-white p-4 text-xs font-bold uppercase tracking-wider text-ink-500">Stok</td>
                            @foreach ($products as $product)
                                <td class="p-4 text-ink-700">{{ $product->stock }} unit</td>
                            @endforeach
                        </tr>

                        @foreach ($attributes as $attribute)
                            <tr class="bg-paper-50/30">
                                <td class="sticky left-0 z-10 bg-white p-4 text-xs font-bold uppercase tracking-wider text-ink-500">
                                    {{ $attribute->name }}
                                    @if ($attribute->unit)
                                        <span class="block text-[10px] font-medium normal-case tracking-normal text-ink-400">{{ $attribute->unit }}</span>
                                    @endif
                                </td>
                                @foreach ($products as $product)
                                    <td class="p-4 text-ink-700">{{ $specMatrix[$product->id][$attribute->id] ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach

                        @if ($attributes->isEmpty())
                            <tr>
                                <td class="sticky left-0 z-10 bg-white p-4 text-xs font-bold uppercase tracking-wider text-ink-500">Spesifikasi</td>
                                <td colspan="{{ $products->count() }}" class="p-4 text-sm text-ink-400">Produk yang dipilih belum memiliki data spesifikasi teknis.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <p class="mt-4 flex items-center gap-2 text-xs text-ink-400">
                <x-icon name="information-circle" class="h-4 w-4" /> Ingin menambah produk lain? Maksimal 3 produk dapat dibandingkan sekaligus.
                <a href="{{ route('products.index') }}" class="font-semibold text-amber-600">Cari produk</a>
            </p>
        @endif
    </div>
</x-layouts.public>
