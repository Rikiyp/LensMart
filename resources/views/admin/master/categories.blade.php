<x-layouts.portal title="Master Kategori" subtitle="Kelola kategori produk yang digunakan di seluruh katalog.">
    <div class="grid gap-5 xl:grid-cols-[340px_1fr]">
        <section class="card p-5">
            <h2 class="text-sm font-extrabold text-ink-950">{{ $editing ? 'Edit kategori' : 'Tambah kategori' }}</h2>
            <p class="mt-1 text-xs text-ink-500">Kategori membantu pembeli menemukan produk.</p>

            <form method="POST" action="{{ $editing ? route('admin.categories.update', $editing) : route('admin.categories.store') }}" class="mt-4 space-y-3">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif
                <div>
                    <label for="name" class="label text-xs">Nama kategori</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $editing?->name) }}" required maxlength="255" class="input !py-2 text-sm" placeholder="Contoh: Kamera Mirrorless">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary btn-sm flex-1">{{ $editing ? 'Simpan perubahan' : 'Tambah kategori' }}</button>
                    @if ($editing)
                        <a href="{{ route('admin.categories.index') }}" class="btn-outline btn-sm justify-center">Batal</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-paper-200 px-5 py-4">
                <div>
                    <h2 class="text-sm font-extrabold text-ink-950">Daftar kategori</h2>
                    <p class="text-xs text-ink-500">{{ $categories->count() }} kategori terdaftar.</p>
                </div>
            </div>

            @if ($categories->isEmpty())
                <div class="p-5"><x-empty-state icon="tag" title="Belum ada kategori" description="Tambahkan kategori pertama melalui formulir di samping." /></div>
            @else
                <div class="divide-y divide-paper-200">
                    @foreach ($categories as $category)
                        <div class="flex flex-wrap items-center gap-3 px-5 py-3.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-paper-100 text-ink-500"><x-icon name="tag" class="h-4 w-4" /></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-ink-950">{{ $category->name }}</p>
                                <p class="text-[11px] text-ink-400">{{ $category->slug }}</p>
                            </div>
                            <span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $category->products_count }} produk</span>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.categories.index', ['edit' => $category->id]) }}" class="btn-outline btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" x-confirm="{ title: 'Hapus kategori ini?', message: 'Kategori akan dihapus dari master data. Kategori yang masih dipakai produk tidak dapat dihapus.', confirm: 'Ya, hapus', tone: 'danger' }">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-full text-ink-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus kategori"><x-icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.portal>
