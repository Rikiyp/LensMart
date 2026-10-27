<x-guest-layout>
    <div>
        <p class="section-eyebrow">Selamat datang kembali</p>
        <h1 class="mt-2 font-display text-4xl font-semibold tracking-tight text-ink-950">Masuk ke akun</h1>
        <p class="mt-2 text-sm text-ink-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-amber-700 underline decoration-amber-500/40 underline-offset-4 hover:text-amber-800">Daftar sebagai pelanggan</a>
        </p>
    </div>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="label">Email</label>
            <div class="relative">
                <x-icon name="envelope" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       placeholder="nama@email.com" class="input pl-11">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div x-data="{ show: false }">
            <div class="flex items-center justify-between">
                <label for="password" class="label">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-amber-700 underline decoration-amber-500/40 underline-offset-4 hover:text-amber-800">Lupa password?</a>
                @endif
            </div>
            <div class="relative">
                <x-icon name="lock-closed" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       placeholder="••••••••" class="input px-11">
                <button type="button" @click="show = ! show" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-ink-400 hover:text-ink-700" aria-label="Tampilkan password">
                    <x-icon name="eye" x-show="! show" class="h-4 w-4" />
                    <x-icon name="eye-slash" x-show="show" x-cloak class="h-4 w-4" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label class="flex items-center gap-2.5 text-sm text-ink-600">
            <input type="checkbox" name="remember" class="rounded border-paper-400 text-ink-950 focus:ring-amber-300">
            Ingat saya di perangkat ini
        </label>

        <button type="submit" class="btn-primary w-full py-3">Masuk</button>
    </form>

    <div class="my-7 flex items-center gap-3">
        <span class="h-px flex-1 bg-paper-300"></span>
        <span class="text-[11px] font-semibold uppercase tracking-widest text-ink-400">atau</span>
        <span class="h-px flex-1 bg-paper-300"></span>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <a href="{{ route('register') }}" class="btn-outline justify-center">Daftar pelanggan</a>
        <a href="{{ route('partner.register') }}" class="btn-outline justify-center">Buka toko</a>
    </div>

    <div class="mt-8 rounded-lg border border-paper-300 bg-white p-4">
        <p class="flex items-center gap-2 text-xs font-bold text-ink-700">
            <x-icon name="information-circle" class="h-4 w-4 text-amber-500" /> Akun demo
        </p>
        <div class="mt-3 grid gap-1.5 text-[11px] text-ink-500">
            <p><span class="font-semibold text-ink-700">Admin:</span> admin@lensmart.test</p>
            <p><span class="font-semibold text-ink-700">Partner:</span> partner1@lensmart.test</p>
            <p><span class="font-semibold text-ink-700">Pelanggan:</span> customer@lensmart.test</p>
            <p>Password semua akun: <span class="font-mono font-semibold text-ink-700">password</span></p>
        </div>
    </div>
</x-guest-layout>
