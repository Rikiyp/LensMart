<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#101014">
    <link rel="icon" href="{{ asset('images/lensmart-mark.svg') }}" type="image/svg+xml">
    <title>{{ config('app.name') }} — Masuk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper-100 font-sans text-ink-950 antialiased">
    <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
        <aside class="relative hidden flex-col overflow-hidden bg-ink-950 p-12 text-white lg:flex">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]"
                 style="background-image: linear-gradient(to right, white 1px, transparent 1px), linear-gradient(to bottom, white 1px, transparent 1px); background-size: 72px 72px;"></div>

            <a href="{{ route('home') }}" class="relative flex items-center" aria-label="LensMart — beranda">
                <x-brand-logo variant="light" size="lg" />
            </a>

            <div class="relative my-auto max-w-lg">
                <p class="flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.24em] text-amber-400">
                    <span class="h-px w-8 bg-amber-400/70"></span> Marketplace fotografi
                </p>
                <h1 class="mt-6 font-display text-[52px] font-semibold leading-[0.98] tracking-[-0.035em]">
                    Gear terbaik<br>untuk <em class="italic text-amber-400">setiap frame.</em>
                </h1>
                <p class="mt-6 max-w-sm text-[13px] leading-6 text-white/50">
                    Bandingkan kamera dan lensa dari toko terverifikasi, kelola pesanan, dan bangun koleksi gear Anda dalam satu tempat.
                </p>

                <div class="mt-12 grid grid-cols-3 gap-4 border-t border-white/10 pt-6">
                    <div>
                        <p class="tabular font-display text-[26px] font-semibold leading-none">5</p>
                        <p class="mt-2 text-[9.5px] uppercase tracking-[0.14em] text-white/35">Toko demo</p>
                    </div>
                    <div>
                        <p class="tabular font-display text-[26px] font-semibold leading-none">30<span class="text-amber-400">+</span></p>
                        <p class="mt-2 text-[9.5px] uppercase tracking-[0.14em] text-white/35">Produk terkurasi</p>
                    </div>
                    <div>
                        <p class="tabular font-display text-[26px] font-semibold leading-none">5<span class="text-amber-400">%</span></p>
                        <p class="mt-2 text-[9.5px] uppercase tracking-[0.14em] text-white/35">Komisi platform</p>
                    </div>
                </div>
            </div>

            <p class="relative text-[10px] uppercase tracking-[0.14em] text-white/25">&copy; {{ now()->year }} LensMart — Lingkungan demo portofolio</p>
        </aside>

        <main class="flex flex-col px-5 py-8 sm:px-10 lg:px-16">
            <div class="flex items-center justify-between lg:hidden">
                <a href="{{ route('home') }}" class="flex items-center" aria-label="LensMart — beranda">
                    <x-brand-logo :tagline="false" />
                </a>
                <a href="{{ route('home') }}" class="text-[11px] font-semibold uppercase tracking-[0.12em] text-ink-500 transition hover:text-ink-950">Kembali ke toko</a>
            </div>

            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-10">
                {{ $slot }}
            </div>

            <p class="mx-auto w-full max-w-md text-center text-[10.5px] leading-5 text-ink-400">
                Dengan melanjutkan, Anda menyetujui ketentuan demo LensMart. Pembayaran disimulasikan.
            </p>
        </main>
    </div>
</body>
</html>
