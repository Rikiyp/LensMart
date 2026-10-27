<x-account-shell :title="$review->exists ? 'Edit Ulasan' : 'Beri Ulasan'" subtitle="Bagikan pengalaman Anda agar membantu pembeli lain.">
    @php
        $isEdit = $review->exists;
        $action = $isEdit ? route('customer.reviews.update', $review) : route('customer.reviews.store', [$order, $product]);
        $currentRating = (int) old('rating', $review->rating ?: 5);
    @endphp

    <div class="mb-5">
        <a href="{{ route('products.show', $product) }}#ulasan" class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-500 hover:text-amber-600">
            <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Kembali ke produk
        </a>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1fr_300px]">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="card p-5 sm:p-6" x-data="{ rating: {{ $currentRating }} }">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="flex items-start gap-4">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-16 w-16 rounded-xl object-cover">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">{{ $product->brand?->name }}</p>
                    <p class="line-clamp-2 text-sm font-bold text-ink-950">{{ $product->name }}</p>
                    <p class="mt-1 text-[11px] text-ink-500">Pesanan {{ $order?->order_number }}</p>
                </div>
            </div>

            <div class="mt-6 border-t border-paper-200 pt-5">
                <label class="label">Rating Anda</label>
                <div class="flex items-center gap-1.5">
                    <template x-for="star in 5" :key="star">
                        <button type="button" @click="rating = star" class="rounded-lg p-1 transition hover:scale-110" :aria-label="'Beri ' + star + ' bintang'">
                            <svg class="h-9 w-9" :class="star <= rating ? 'text-amber-400' : 'text-paper-300'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 0 0-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.539 1.118l-3.366-2.446a1 1 0 0 0-1.176 0l-3.366 2.446c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 0 0-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 0 0 .95-.69l1.286-3.958Z" />
                            </svg>
                        </button>
                    </template>
                    <span class="ml-2 text-sm font-bold text-ink-700" x-text="rating + ' / 5'"></span>
                </div>
                <input type="hidden" name="rating" :value="rating">
                <x-input-error :messages="$errors->get('rating')" class="mt-2" />
            </div>

            <div class="mt-5">
                <label for="comment" class="label">Ulasan <span class="font-normal text-ink-400">(opsional)</span></label>
                <textarea id="comment" name="comment" rows="5" maxlength="2000" class="textarea" placeholder="Ceritakan kualitas produk, kondisi barang, dan pengalaman berbelanja Anda…">{{ old('comment', $review->comment) }}</textarea>
                <x-input-error :messages="$errors->get('comment')" class="mt-2" />
            </div>

            @if ($isEdit && $review->images->isNotEmpty())
                <div class="mt-5">
                    <p class="label">Foto saat ini</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($review->images as $image)
                            <label class="group relative h-20 w-20 cursor-pointer overflow-hidden rounded-xl border border-paper-300">
                                <img src="{{ $image->url }}" alt="Foto ulasan" class="h-full w-full object-cover">
                                <span class="absolute inset-0 flex items-center justify-center bg-ink-950/60 opacity-0 transition group-hover:opacity-100">
                                    <span class="flex items-center gap-1 text-[10px] font-bold text-white">
                                        <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="rounded border-white/50 bg-white/20 text-rose-500 focus:ring-0">
                                        Hapus
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-1.5 text-[11px] text-ink-400">Centang foto untuk menghapusnya.</p>
                </div>
            @endif

            <div class="mt-5">
                <label for="images" class="label">Tambah foto <span class="font-normal text-ink-400">(maks. 3 foto, 2 MB per foto)</span></label>
                <input id="images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple
                       class="block w-full rounded-xl border border-dashed border-paper-400 bg-white px-4 py-3 text-xs text-ink-500 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-950 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                <x-input-error :messages="$errors->get('images')" class="mt-2" />
                <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col gap-2 border-t border-paper-200 pt-5 sm:flex-row">
                <button type="submit" class="btn-primary sm:flex-1">
                    <x-icon name="star" class="h-4 w-4" /> {{ $isEdit ? 'Simpan perubahan' : 'Kirim ulasan' }}
                </button>
                <a href="{{ route('products.show', $product) }}#ulasan" class="btn-outline justify-center">Batal</a>
            </div>
        </form>

        <aside class="space-y-4">
            <div class="rounded-2xl bg-amber-50 p-4 text-xs leading-relaxed text-amber-900 ring-1 ring-inset ring-amber-600/20">
                <p class="flex items-start gap-2">
                    <x-icon name="information-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>Ulasan hanya dapat ditulis satu kali per produk per pesanan. Anda dapat mengeditnya selama ulasan tidak disembunyikan admin.</span>
                </p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-bold text-ink-950">Tips menulis ulasan</p>
                <ul class="mt-2 space-y-1.5 text-[11px] leading-relaxed text-ink-500">
                    <li>• Jelaskan kondisi barang sesuai deskripsi.</li>
                    <li>• Sebutkan kelebihan dan kekurangan yang Anda rasakan.</li>
                    <li>• Unggah foto asli untuk membantu pembeli lain.</li>
                </ul>
            </div>
        </aside>
    </div>
</x-account-shell>
