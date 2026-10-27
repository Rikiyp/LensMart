<x-guest-layout>
    <div>
        <p class="section-eyebrow">Akun pelanggan</p>
        <h1 class="mt-2 font-display text-4xl font-semibold tracking-tight text-ink-950">Buat akun baru</h1>
        <p class="mt-2 text-sm text-ink-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-amber-600 hover:text-amber-700">Masuk di sini</a>
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="name" class="label">Nama lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   placeholder="Nama Anda" class="input">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   placeholder="nama@email.com" class="input">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   placeholder="Minimal 8 karakter" class="input">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="label">Konfirmasi password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   placeholder="Ulangi password" class="input">
        </div>

        <label class="flex items-start gap-2.5 text-xs leading-relaxed text-ink-500">
            <input type="checkbox" required class="mt-0.5 rounded border-paper-400 text-ink-950 focus:ring-amber-300">
            Saya memahami ini adalah aplikasi demo dan data dapat direset kapan saja.
        </label>

        <button type="submit" class="btn-primary w-full py-3">Daftar sekarang</button>
    </form>

    <div class="mt-6 rounded-2xl bg-amber-50 p-4 text-xs leading-relaxed text-amber-900 ring-1 ring-inset ring-amber-600/20">
        <p class="flex items-start gap-2">
            <x-icon name="store" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>Ingin menjual kamera? <a href="{{ route('partner.register') }}" class="font-bold underline underline-offset-2">Daftar sebagai partner toko</a> dan lengkapi verifikasi.</span>
        </p>
    </div>
</x-guest-layout>
