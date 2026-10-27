<x-layouts.portal title="Profil Toko" subtitle="Kelola informasi publik dan data legalitas toko Anda.">
    @php
        $isRejected = $store->status === \App\Enums\StoreStatus::Rejected;
    @endphp

    <div class="mb-5 flex flex-wrap items-center gap-3">
        <span class="badge {{ $store->status->badge() }}">{{ $store->status->label() }}</span>
        <span class="text-xs text-ink-500">Bergabung {{ format_tanggal($store->created_at) }}</span>
        @if ($store->isVerified())
            <span class="flex items-center gap-1 text-xs font-semibold text-emerald-600">
                <x-icon name="shield-check" class="h-4 w-4" /> Toko terverifikasi
            </span>
        @endif
    </div>

    @if ($store->rejection_note)
        <div class="mb-5 flex items-start gap-3 rounded-2xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-inset ring-rose-600/20">
            <x-icon name="exclamation-circle" class="mt-0.5 h-5 w-5 shrink-0" />
            <div>
                <p class="font-bold">Catatan penolakan admin</p>
                <p class="mt-0.5 text-xs leading-relaxed">{{ $store->rejection_note }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('partner.store.update') }}" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[1fr_320px]">
        @csrf
        @method('PUT')

        <div class="space-y-5">
            <section class="card p-5 sm:p-6">
                <div class="border-b border-paper-200 pb-4">
                    <h2 class="text-sm font-extrabold text-ink-950">Informasi toko</h2>
                    <p class="mt-1 text-xs text-ink-500">Informasi ini tampil pada halaman produk dan checkout.</p>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">Nama toko</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $store->name) }}" required maxlength="255" class="input">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="label">Deskripsi toko</label>
                        <textarea id="description" name="description" rows="3" maxlength="2000" class="textarea" placeholder="Ceritakan spesialisasi dan keunggulan toko Anda">{{ old('description', $store->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                    <div>
                        <label for="city" class="label">Kota</label>
                        <input id="city" type="text" name="city" value="{{ old('city', $store->city) }}" required maxlength="100" class="input">
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    </div>
                    <div>
                        <label for="phone" class="label">Nomor telepon toko</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $store->phone) }}" required maxlength="30" class="input">
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="label">Alamat toko</label>
                        <textarea id="address" name="address" rows="2" required maxlength="1000" class="textarea">{{ old('address', $store->address) }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <div class="border-b border-paper-200 pb-4">
                    <h2 class="text-sm font-extrabold text-ink-950">Rekening pencairan</h2>
                    <p class="mt-1 text-xs text-ink-500">Dana penjualan akan dicairkan ke rekening ini setelah pesanan selesai.</p>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="bank_name" class="label">Nama bank</label>
                        <input id="bank_name" type="text" name="bank_name" value="{{ old('bank_name', $store->bank_name) }}" maxlength="100" class="input" placeholder="Contoh: BCA">
                        <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="account_number" class="label">Nomor rekening</label>
                        <input id="account_number" type="text" name="account_number" value="{{ old('account_number', $store->account_number) }}" maxlength="50" class="input">
                        <x-input-error :messages="$errors->get('account_number')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="account_holder" class="label">Nama pemilik rekening</label>
                        <input id="account_holder" type="text" name="account_holder" value="{{ old('account_holder', $store->account_holder) }}" maxlength="255" class="input">
                        <x-input-error :messages="$errors->get('account_holder')" class="mt-2" />
                    </div>
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <div class="border-b border-paper-200 pb-4">
                    <h2 class="text-sm font-extrabold text-ink-950">Berkas toko</h2>
                    <p class="mt-1 text-xs text-ink-500">Unggah logo dan dokumen legalitas untuk verifikasi.</p>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="logo" class="label">Logo toko <span class="font-normal text-ink-400">(maks. 2 MB)</span></label>
                        <input id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp"
                               class="block w-full rounded-xl border border-dashed border-paper-400 bg-white px-4 py-2.5 text-xs text-ink-500 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-950 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                        <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        @if ($store->logo)
                            <p class="mt-2 text-[11px] text-ink-400">Logo saat ini: <a href="{{ $store->logo_url }}" target="_blank" class="font-semibold text-amber-600">lihat</a></p>
                        @endif
                    </div>
                    <div>
                        <label for="legal_document" class="label">Dokumen legalitas <span class="font-normal text-ink-400">(PDF/JPG/PNG, maks. 5 MB)</span></label>
                        <input id="legal_document" type="file" name="legal_document" accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full rounded-xl border border-dashed border-paper-400 bg-white px-4 py-2.5 text-xs text-ink-500 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-950 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                        <x-input-error :messages="$errors->get('legal_document')" class="mt-2" />
                        @if ($store->legal_document)
                            <p class="mt-2 text-[11px] text-ink-400">Dokumen saat ini: <a href="{{ asset('storage/'.$store->legal_document) }}" target="_blank" class="font-semibold text-amber-600">lihat</a></p>
                        @endif
                    </div>
                </div>
            </section>

            @if ($isRejected)
                <div class="flex items-start gap-3 rounded-2xl bg-amber-50 p-4 text-sm text-amber-900 ring-1 ring-inset ring-amber-600/20">
                    <x-icon name="arrow-path" class="mt-0.5 h-5 w-5 shrink-0" />
                    <label class="flex cursor-pointer items-start gap-2.5">
                        <input type="checkbox" name="resubmit" value="1" class="mt-0.5 rounded border-amber-300 text-amber-500 focus:ring-amber-400">
                        <span class="text-xs leading-relaxed">
                            <span class="block font-bold">Ajukan verifikasi ulang</span>
                            Centang setelah memperbaiki profil dan dokumen. Status toko akan kembali menjadi menunggu verifikasi.
                        </span>
                    </label>
                </div>
            @endif
        </div>

        <aside class="space-y-4">
            <div class="card p-5">
                <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="h-20 w-20 rounded-2xl object-cover ring-2 ring-paper-200">
                <p class="mt-3 text-sm font-extrabold text-ink-950">{{ $store->name }}</p>
                <p class="text-xs text-ink-500">{{ $store->city }}</p>
                <div class="mt-3 border-t border-paper-200 pt-3">
                    <span class="badge {{ $store->status->badge() }}">{{ $store->status->label() }}</span>
                </div>
            </div>

            <div class="rounded-2xl bg-sky-50 p-4 text-[11px] leading-relaxed text-sky-900 ring-1 ring-inset ring-sky-600/20">
                <p class="flex items-start gap-2">
                    <x-icon name="information-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>Perubahan nama toko tidak mengubah URL publik. Verifikasi ulang hanya diperlukan jika status toko ditolak.</span>
                </p>
            </div>

            <button type="submit" class="btn-primary w-full">
                <x-icon name="check" class="h-4 w-4" /> Simpan profil toko
            </button>
        </aside>
    </form>
</x-layouts.portal>
