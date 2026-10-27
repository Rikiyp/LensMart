<x-guest-layout>
    <div>
        <p class="section-eyebrow">Partner toko</p>
        <h1 class="mt-2 font-display text-4xl font-semibold tracking-tight text-ink-950">Buka toko Anda</h1>
        <p class="mt-2 text-sm text-ink-500">Daftarkan toko kamera Anda. Tim admin akan memverifikasi dokumen dan profil toko.</p>
    </div>

    <div class="mt-5 flex items-start gap-2 rounded-2xl bg-sky-50 p-3 text-[11px] leading-relaxed text-sky-900 ring-1 ring-inset ring-sky-600/20">
        <x-icon name="information-circle" class="mt-0.5 h-4 w-4 shrink-0" />
        <span>Setelah mendaftar, Anda dapat melengkapi profil dan mengunggah dokumen legalitas. Produk baru bisa ditambahkan setelah toko terverifikasi.</span>
    </div>

    <form method="POST" action="{{ route('partner.register.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        <fieldset class="space-y-4">
            <legend class="text-xs font-bold uppercase tracking-[0.18em] text-ink-400">Akun pemilik</legend>
            <div>
                <label for="name" class="label">Nama lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="input" placeholder="Nama pemilik toko">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input" placeholder="toko@email.com">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <label for="phone" class="label">Nomor telepon</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required class="input" placeholder="08xxxxxxxxxx">
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
            </div>
        </fieldset>

        <fieldset class="space-y-4 border-t border-paper-300 pt-5">
            <legend class="text-xs font-bold uppercase tracking-[0.18em] text-ink-400">Profil toko</legend>
            <div>
                <label for="store_name" class="label">Nama toko</label>
                <input id="store_name" type="text" name="store_name" value="{{ old('store_name') }}" required class="input" placeholder="Contoh: Cahaya Kamera Studio">
                <x-input-error :messages="$errors->get('store_name')" class="mt-2" />
            </div>
            <div>
                <label for="city" class="label">Kota</label>
                <input id="city" type="text" name="city" value="{{ old('city') }}" required class="input" placeholder="Jakarta Selatan">
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div>
                <label for="address" class="label">Alamat toko <span class="font-normal text-ink-400">(opsional)</span></label>
                <textarea id="address" name="address" rows="2" class="textarea" placeholder="Alamat lengkap toko">{{ old('address') }}</textarea>
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>
            <div>
                <label for="description" class="label">Deskripsi toko <span class="font-normal text-ink-400">(opsional)</span></label>
                <textarea id="description" name="description" rows="2" class="textarea" placeholder="Ceritakan spesialisasi toko Anda">{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="legal_document" class="label">Dokumen legalitas <span class="font-normal text-ink-400">(opsional, PDF/JPG/PNG maks 5 MB)</span></label>
                <input id="legal_document" type="file" name="legal_document" accept=".pdf,.jpg,.jpeg,.png" class="block w-full rounded-lg border border-dashed border-paper-400 bg-white px-4 py-3 text-xs text-ink-500 file:mr-3 file:rounded-md file:border-0 file:bg-ink-950 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                <x-input-error :messages="$errors->get('legal_document')" class="mt-2" />
            </div>
        </fieldset>

        <fieldset class="space-y-4 border-t border-paper-300 pt-5">
            <legend class="text-xs font-bold uppercase tracking-[0.18em] text-ink-400">Keamanan akun</legend>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="label">Password</label>
                    <input id="password" type="password" name="password" required class="input" placeholder="Minimal 8 karakter">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div>
                    <label for="password_confirmation" class="label">Konfirmasi password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="input" placeholder="Ulangi password">
                </div>
            </div>
        </fieldset>

        <button type="submit" class="btn-accent w-full py-3">Daftar sebagai partner</button>
    </form>

    <p class="mt-6 text-center text-xs text-ink-500">
        Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-amber-600">Masuk</a>
    </p>
</x-guest-layout>
