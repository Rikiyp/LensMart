<x-layouts.portal :title="$product->exists ? 'Edit Produk' : 'Tambah Produk'" subtitle="Lengkapi detail produk, spesifikasi, dan foto.">
    @php
        $isEdit = $product->exists;
        $initialCompat = old('compatibilities', $compatibilities->map(fn ($item) => [
            'brand_id' => (string) $item->brand_id,
            'model_name' => $item->model_name,
        ])->values()->all());
        $conditionValue = old('condition', $product->condition?->value ?? \App\Enums\ProductCondition::New->value);
        $statusValue = old('status', $product->status?->value ?? \App\Enums\ProductStatus::Active->value);
    @endphp

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('partner.products.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-500 hover:text-amber-600">
            <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Kembali ke produk
        </a>
        @if ($isEdit)
            <div class="flex items-center gap-2">
                <span class="badge {{ $product->verification_status->badge() }}">{{ $product->verification_status->label() }}</span>
                @if ($product->isPubliclyVisible())
                    <a href="{{ route('products.show', $product) }}" target="_blank" class="text-xs font-bold text-amber-600">Lihat di katalog</a>
                @endif
            </div>
        @endif
    </div>

    <form method="POST" action="{{ $isEdit ? route('partner.products.update', $product) : route('partner.products.store') }}"
          enctype="multipart/form-data" class="space-y-5" x-data="{ compat: @js($initialCompat) }">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="grid gap-5 xl:grid-cols-[1fr_330px]">
            <div class="space-y-5">
                <section class="card p-5 sm:p-6">
                    <div class="border-b border-paper-200 pb-4">
                        <h2 class="text-sm font-extrabold text-ink-950">Informasi produk</h2>
                        <p class="mt-1 text-xs text-ink-500">Nama, kategori, dan deskripsi yang jelas membantu pembeli menemukan produk.</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="label">Nama produk</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required maxlength="255" class="input" placeholder="Contoh: Sony Alpha A7 III Body Only">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <label for="category_id" class="label">Kategori</label>
                            <select id="category_id" name="category_id" required class="select">
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="brand_id" class="label">Merek</label>
                            <select id="brand_id" name="brand_id" required class="select">
                                <option value="">Pilih merek</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('brand_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="model_name" class="label">Nama model</label>
                            <input id="model_name" type="text" name="model_name" value="{{ old('model_name', $product->model_name) }}" required maxlength="255" class="input" placeholder="Contoh: ILCE-7M3">
                            <x-input-error :messages="$errors->get('model_name')" class="mt-2" />
                        </div>

                        <div>
                            <label for="condition" class="label">Kondisi</label>
                            <select id="condition" name="condition" required class="select">
                                @foreach (\App\Enums\ProductCondition::cases() as $condition)
                                    <option value="{{ $condition->value }}" @selected($conditionValue === $condition->value)>{{ $condition->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('condition')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="label">Deskripsi</label>
                            <textarea id="description" name="description" rows="5" maxlength="5000" class="textarea" placeholder="Jelaskan kondisi, kelengkapan, dan detail penting produk…">{{ old('description', $product->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section class="card p-5 sm:p-6">
                    <div class="border-b border-paper-200 pb-4">
                        <h2 class="text-sm font-extrabold text-ink-950">Spesifikasi teknis</h2>
                        <p class="mt-1 text-xs text-ink-500">Opsional, tetapi sangat membantu perbandingan produk.</p>
                    </div>

                    @php
                        $groupedAttributes = $specAttributes->groupBy(fn ($attribute) => $attribute->category?->name ?? 'Umum');
                    @endphp
                    <div class="mt-5 space-y-6">
                        @foreach ($groupedAttributes as $groupName => $attributes)
                            <div>
                                <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-ink-400">{{ $groupName }}</p>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($attributes as $attribute)
                                        <div>
                                            <label for="spec-{{ $attribute->id }}" class="mb-1 block text-xs font-semibold text-ink-600">
                                                {{ $attribute->name }}
                                                @if ($attribute->unit)
                                                    <span class="font-normal text-ink-400">({{ $attribute->unit }})</span>
                                                @endif
                                            </label>
                                            <input id="spec-{{ $attribute->id }}" type="text" name="specs[{{ $attribute->id }}]"
                                                   value="{{ old('specs.'.$attribute->id, $specValues[$attribute->id] ?? '') }}"
                                                   maxlength="255" class="input !py-2 text-xs" placeholder="Nilai">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        @if ($specAttributes->isEmpty())
                            <p class="text-xs text-ink-400">Belum ada atribut spesifikasi. Admin dapat menambahkannya di master data.</p>
                        @endif
                    </div>
                </section>

                <section class="card p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-3 border-b border-paper-200 pb-4">
                        <div>
                            <h2 class="text-sm font-extrabold text-ink-950">Kompatibilitas</h2>
                            <p class="mt-1 text-xs text-ink-500">Untuk lensa atau aksesori, sebutkan kamera yang kompatibel.</p>
                        </div>
                        <button type="button" @click="compat.push({ brand_id: '', model_name: '' })" class="btn-outline btn-sm shrink-0">
                            <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah
                        </button>
                    </div>

                    <div class="mt-4 space-y-2">
                        <template x-for="(item, index) in compat" :key="index">
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <select :name="'compatibilities[' + index + '][brand_id]'" x-model="item.brand_id" class="select !py-2 text-xs sm:w-48">
                                    <option value="">Pilih merek</option>
                                    <template x-for="brand in @js($brands->map(fn ($brand) => ['id' => (string) $brand->id, 'name' => $brand->name])->values())" :key="brand.id">
                                        <option :value="brand.id" x-text="brand.name"></option>
                                    </template>
                                </select>
                                <input type="text" :name="'compatibilities[' + index + '][model_name]'" x-model="item.model_name" maxlength="255" class="input !py-2 flex-1 text-xs" placeholder="Nama model kamera">
                                <button type="button" @click="compat.splice(index, 1)" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </template>
                        <p x-show="compat.length === 0" class="text-xs text-ink-400">Belum ada kompatibilitas ditambahkan.</p>
                    </div>
                </section>
            </div>

            <aside class="space-y-5">
                <section class="card p-5">
                    <h2 class="text-sm font-extrabold text-ink-950">Harga &amp; stok</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="price" class="label">Harga (Rp)</label>
                            <input id="price" type="number" name="price" value="{{ old('price', $product->price) }}" required min="1000" step="1000" class="input">
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>
                        <div>
                            <label for="stock" class="label">Stok</label>
                            <input id="stock" type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required min="0" step="1" class="input">
                            <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                        </div>
                        <div>
                            <label for="status" class="label">Status tayang</label>
                            <select id="status" name="status" required class="select">
                                <option value="active" @selected($statusValue === 'active')>Aktif</option>
                                <option value="inactive" @selected($statusValue === 'inactive')>Nonaktif</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section class="card p-5">
                    <h2 class="text-sm font-extrabold text-ink-950">Foto produk</h2>
                    <p class="mt-1 text-[11px] text-ink-500">Maksimal 5 foto. Foto pertama menjadi gambar utama.</p>

                    @if ($isEdit && $product->images->isNotEmpty())
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach ($product->images as $image)
                                <label class="group relative block aspect-square cursor-pointer overflow-hidden rounded-xl border border-paper-300">
                                    <img src="{{ $image->url }}" alt="Foto produk" class="h-full w-full object-cover">
                                    @if ($image->is_primary)
                                        <span class="absolute left-1 top-1 rounded-full bg-amber-400 px-1.5 py-0.5 text-[8px] font-bold text-ink-950">Utama</span>
                                    @endif
                                    <span class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-ink-950/55 opacity-0 transition group-hover:opacity-100">
                                        <span class="flex items-center gap-1 text-[9px] font-bold text-white">
                                            <input type="radio" name="primary_image_id" value="{{ $image->id }}" class="h-3 w-3 border-white/50 bg-white/20 text-amber-400 focus:ring-0" @checked($image->is_primary)>
                                            Utama
                                        </span>
                                        <span class="flex items-center gap-1 text-[9px] font-bold text-white">
                                            <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="h-3 w-3 rounded border-white/50 bg-white/20 text-rose-500 focus:ring-0">
                                            Hapus
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-3">
                        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple
                               class="block w-full rounded-xl border border-dashed border-paper-400 bg-white px-3 py-2.5 text-[11px] text-ink-500 file:mr-2 file:rounded-lg file:border-0 file:bg-ink-950 file:px-2.5 file:py-1.5 file:text-[10px] file:font-semibold file:text-white">
                        <x-input-error :messages="$errors->get('images')" class="mt-2" />
                        <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
                    </div>
                </section>

                @if ($isEdit && $product->rejection_note)
                    <div class="rounded-2xl bg-rose-50 p-4 text-xs leading-relaxed text-rose-800 ring-1 ring-inset ring-rose-600/20">
                        <p class="font-bold">Catatan penolakan</p>
                        <p class="mt-1">{{ $product->rejection_note }}</p>
                        <p class="mt-2 text-[11px] text-rose-600">Simpan perubahan untuk mengajukan ulang verifikasi.</p>
                    </div>
                @endif

                <div class="space-y-2">
                    <button type="submit" class="btn-primary w-full">
                        <x-icon name="check" class="h-4 w-4" /> {{ $isEdit ? 'Simpan perubahan' : 'Simpan produk' }}
                    </button>
                    <a href="{{ route('partner.products.index') }}" class="btn-outline w-full justify-center">Batal</a>
                </div>
            </aside>
        </div>
    </form>
</x-layouts.portal>
