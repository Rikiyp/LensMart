<x-layouts.portal title="Master Merek" subtitle="Kelola merek kamera, lensa, dan aksesori.">
    <div class="grid gap-5 xl:grid-cols-[340px_1fr]">
        <section class="card p-5">
            <h2 class="text-sm font-extrabold text-ink-950">{{ $editing ? 'Edit merek' : 'Tambah merek' }}</h2>
            <p class="mt-1 text-xs text-ink-500">Merek digunakan pada produk dan kompatibilitas.</p>

            <form method="POST" action="{{ $editing ? route('admin.brands.update', $editing) : route('admin.brands.store') }}" class="mt-4 space-y-3">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif
                <div>
                    <label for="name" class="label text-xs">Nama merek</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $editing?->name) }}" required maxlength="255" class="input !py-2 text-sm" placeholder="Contoh: Sony">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary btn-sm flex-1">{{ $editing ? 'Simpan perubahan' : 'Tambah merek' }}</button>
                    @if ($editing)
                        <a href="{{ route('admin.brands.index') }}" class="btn-outline btn-sm justify-center">Batal</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-paper-200 px-5 py-4">
                <div>
                    <h2 class="text-sm font-extrabold text-ink-950">Daftar merek</h2>
                    <p class="text-xs text-ink-500">{{ $brands->count() }} merek terdaftar.</p>
                </div>
            </div>

            @if ($brands->isEmpty())
                <div class="p-5"><x-empty-state icon="sparkles" title="Belum ada merek" description="Tambahkan merek pertama melalui formulir di samping." /></div>
            @else
                <div class="divide-y divide-paper-200">
                    @foreach ($brands as $brand)
                        <div class="flex flex-wrap items-center gap-3 px-5 py-3.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><x-icon name="sparkles" class="h-4 w-4" /></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-ink-950">{{ $brand->name }}</p>
                                <p class="text-[11px] text-ink-400">{{ $brand->slug }}</p>
                            </div>
                            <span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $brand->products_count }} produk</span>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.brands.index', ['edit' => $brand->id]) }}" class="btn-outline btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" x-confirm="{ title: 'Hapus merek ini?', message: 'Merek akan dihapus dari master data. Merek yang masih dipakai produk tidak dapat dihapus.', confirm: 'Ya, hapus', tone: 'danger' }">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-full text-ink-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus merek"><x-icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.portal>
