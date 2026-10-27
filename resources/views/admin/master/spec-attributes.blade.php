<x-layouts.portal title="Atribut Spesifikasi" subtitle="Kelola atribut teknis yang dapat diisi pada produk.">
    <div class="grid gap-5 xl:grid-cols-[360px_1fr]">
        <section class="card p-5">
            <h2 class="text-sm font-extrabold text-ink-950">{{ $editing ? 'Edit atribut' : 'Tambah atribut' }}</h2>
            <p class="mt-1 text-xs text-ink-500">Atribut membantu pembeli membandingkan spesifikasi.</p>

            <form method="POST" action="{{ $editing ? route('admin.spec-attributes.update', $editing) : route('admin.spec-attributes.store') }}" class="mt-4 space-y-3">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif
                <div>
                    <label for="name" class="label text-xs">Nama atribut</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $editing?->name) }}" required maxlength="255" class="input !py-2 text-sm" placeholder="Contoh: Resolusi sensor">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <label for="unit" class="label text-xs">Satuan <span class="font-normal text-ink-400">(opsional)</span></label>
                    <input id="unit" type="text" name="unit" value="{{ old('unit', $editing?->unit) }}" maxlength="50" class="input !py-2 text-sm" placeholder="Contoh: MP">
                    <x-input-error :messages="$errors->get('unit')" class="mt-1" />
                </div>
                <div>
                    <label for="category_id" class="label text-xs">Kategori <span class="font-normal text-ink-400">(opsional)</span></label>
                    <select id="category_id" name="category_id" class="select !py-2 text-sm">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $editing?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary btn-sm flex-1">{{ $editing ? 'Simpan perubahan' : 'Tambah atribut' }}</button>
                    @if ($editing)
                        <a href="{{ route('admin.spec-attributes.index') }}" class="btn-outline btn-sm justify-center">Batal</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-paper-200 px-5 py-4">
                <div>
                    <h2 class="text-sm font-extrabold text-ink-950">Daftar atribut</h2>
                    <p class="text-xs text-ink-500">{{ $specAttributes->count() }} atribut terdaftar.</p>
                </div>
            </div>

            @if ($specAttributes->isEmpty())
                <div class="p-5"><x-empty-state icon="clipboard" title="Belum ada atribut" description="Tambahkan atribut pertama melalui formulir di samping." /></div>
            @else
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Atribut</th>
                                <th>Kategori</th>
                                <th>Produk</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($specAttributes as $attribute)
                                <tr>
                                    <td>
                                        <p class="text-sm font-bold text-ink-950">{{ $attribute->name }}</p>
                                        <p class="text-[11px] text-ink-400">{{ $attribute->unit ?: 'Tanpa satuan' }}</p>
                                    </td>
                                    <td class="text-xs text-ink-600">{{ $attribute->category?->name ?? 'Semua kategori' }}</td>
                                    <td><span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $attribute->products_count }} produk</span></td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.spec-attributes.index', ['edit' => $attribute->id]) }}" class="btn-outline btn-sm">Edit</a>
                                            <form method="POST" action="{{ route('admin.spec-attributes.destroy', $attribute) }}" x-confirm="{ title: 'Hapus atribut ini?', message: 'Atribut akan dihapus dari master data. Atribut yang masih dipakai produk tidak dapat dihapus.', confirm: 'Ya, hapus', tone: 'danger' }">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-full text-ink-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus atribut"><x-icon name="trash" class="h-4 w-4" /></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.portal>
