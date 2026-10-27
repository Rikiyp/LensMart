<x-layouts.portal title="Produk Saya" subtitle="Kelola katalog, stok, dan status verifikasi produk.">
    @php
        $store = auth()->user()->store;
    @endphp

    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs text-ink-500">{{ $products->total() }} produk terdaftar</p>
            @if ($store && ! $store->isVerified())
                <p class="mt-1 text-[11px] font-semibold text-amber-600">Toko belum terverifikasi — produk baru dapat ditambahkan setelah admin menyetujui toko.</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('products.index') }}" class="btn-outline btn-sm" target="_blank">
                <x-icon name="arrow-top-right" class="h-3.5 w-3.5" /> Lihat katalog
            </a>
            @if ($store?->isVerified())
                <a href="{{ route('partner.products.create') }}" class="btn-primary btn-sm">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah produk
                </a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('partner.products.index') }}" class="card mb-5 flex flex-col gap-2 p-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama produk…" class="input !py-2 pl-10 text-xs">
        </div>
        <select name="verification" class="select !py-2 text-xs sm:w-48">
            <option value="">Semua verifikasi</option>
            @foreach ($verifications as $verification)
                <option value="{{ $verification->value }}" @selected(request('verification') === $verification->value)>{{ $verification->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary btn-sm">Filter</button>
        @if (request()->filled('q') || request()->filled('verification'))
            <a href="{{ route('partner.products.index') }}" class="btn-ghost btn-sm justify-center">Reset</a>
        @endif
    </form>

    @if ($products->isEmpty())
        <x-empty-state icon="cube" title="Belum ada produk" description="Tambahkan produk pertama Anda agar pembeli dapat menemukannya di katalog.">
            @if ($store?->isVerified())
                <a href="{{ route('partner.products.create') }}" class="btn-primary btn-sm">Tambah produk</a>
            @else
                <a href="{{ route('partner.store.edit') }}" class="btn-primary btn-sm">Lengkapi profil toko</a>
            @endif
        </x-empty-state>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Verifikasi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td class="min-w-[240px]">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-12 w-12 rounded-xl object-cover ring-1 ring-paper-300">
                                        <div class="min-w-0">
                                            <p class="line-clamp-1 text-sm font-bold text-ink-950">{{ $product->name }}</p>
                                            <p class="mt-0.5 text-[11px] text-ink-400">{{ $product->brand?->name }} · {{ $product->category?->name }}</p>
                                            <p class="text-[10px] text-ink-400">{{ $product->reviews_count }} ulasan</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-sm font-bold text-ink-950">{{ format_rupiah($product->price) }}</td>
                                <td>
                                    <span class="badge {{ $product->stock <= 3 ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-paper-100 text-ink-600 ring-ink-500/15' }}">{{ $product->stock }} unit</span>
                                </td>
                                <td><span class="badge {{ $product->status->badge() }}">{{ $product->status->label() }}</span></td>
                                <td>
                                    <span class="badge {{ $product->verification_status->badge() }}">{{ $product->verification_status->label() }}</span>
                                    @if ($product->rejection_note)
                                        <p class="mt-1 max-w-[180px] text-[10px] leading-snug text-rose-500">{{ $product->rejection_note }}</p>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('partner.products.edit', $product) }}" class="btn-outline btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('partner.products.destroy', $product) }}" x-confirm="{ title: 'Hapus produk ini?', message: 'Produk akan dihapus dari toko Anda dan tidak dapat dipulihkan.', confirm: 'Ya, hapus', tone: 'danger' }">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-full text-ink-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus produk">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $products->links() }}</div>
    @endif
</x-layouts.portal>
