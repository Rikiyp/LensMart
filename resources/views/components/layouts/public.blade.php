@props(['title' => null, 'description' => null])

@php
    $user = auth()->user();
    $isCustomer = $user?->isCustomer();
    $cartCount = $isCustomer ? \App\Models\CartItem::where('user_id', $user->id)->sum('quantity') : 0;
    $wishlistCount = $isCustomer ? \App\Models\Wishlist::where('user_id', $user->id)->count() : 0;
    $compareCount = count(session('compare', []));
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $fullTitle = $title ? $title.' — '.config('app.name') : config('app.name').' — Kamera, Lensa & Aksesori Fotografi';
    $navCategories = \App\Models\Category::query()->orderBy('name')->get(['id', 'name']);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#101014">
    <link rel="icon" href="{{ asset('images/lensmart-mark.svg') }}" type="image/svg+xml">
    <meta name="description" content="{{ $description ?? 'LensMart adalah marketplace kamera, lensa, dan aksesori fotografi dari toko terverifikasi. Bandingkan harga, belanja aman, dan temukan gear impianmu.' }}">
    <title>{{ $fullTitle }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-paper-100 font-sans text-ink-950 antialiased">
    {{-- Utility strip --}}
    <div class="bg-ink-950 text-white">
        <div class="mx-auto flex h-9 max-w-7xl items-center justify-between gap-4 px-4 text-[10.5px] font-medium uppercase tracking-[0.14em] sm:px-6 lg:px-8">
            <p class="flex items-center gap-2 whitespace-nowrap text-white/60">
                <x-icon name="shield-check" class="h-3.5 w-3.5 text-amber-400" />
                <span class="hidden sm:inline">Kurasi toko kamera terverifikasi</span>
                <span class="sm:hidden">Toko terverifikasi</span>
                <span class="hidden text-white/25 sm:inline">/</span>
                <span class="hidden sm:inline">Jakarta, Indonesia</span>
            </p>
            <div class="flex items-center gap-4 whitespace-nowrap">
                <span class="hidden items-center gap-1.5 text-white/40 lg:flex">
                    <span class="h-1 w-1 rounded-full bg-amber-400"></span> Lingkungan demo
                </span>
                @guest
                    <a href="{{ route('partner.register') }}" class="hidden text-white/70 transition hover:text-amber-300 sm:inline">Jual di LensMart</a>
                @endguest
                <a href="{{ route('products.index') }}" class="text-white/70 transition hover:text-amber-300">Bantuan</a>
            </div>
        </div>
    </div>

    <header x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-paper-300 bg-paper-100/90 backdrop-blur-md">
        <div class="mx-auto flex h-[68px] max-w-7xl items-center gap-3 px-4 sm:px-6 lg:gap-8 lg:px-8">
            <button type="button" @click="mobileOpen = ! mobileOpen" class="-ml-2 flex h-9 w-9 items-center justify-center rounded-lg text-ink-600 hover:bg-ink-950/5 lg:hidden" aria-label="Buka menu">
                <x-icon name="bars-3" class="h-5 w-5" />
            </button>

            <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="LensMart — beranda">
                <x-brand-logo />
            </a>

            <nav class="hidden items-center gap-7 lg:flex">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Beranda</a>
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'nav-link-active' : '' }}">Katalog</a>
                <a href="{{ route('compare.index') }}" class="nav-link {{ request()->routeIs('compare.*') ? 'nav-link-active' : '' }}">
                    Bandingkan
                    @if ($compareCount)
                        <span class="ml-1.5 rounded bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold text-ink-950">{{ $compareCount }}</span>
                    @endif
                </a>
            </nav>

            <form action="{{ route('products.index') }}" method="GET" class="relative ml-auto hidden w-full max-w-sm md:block">
                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari kamera, lensa, merek…"
                       class="w-full rounded-lg border border-paper-400 bg-white py-2 pl-10 pr-10 text-[13px] text-ink-900 placeholder:text-ink-400 focus:border-ink-950/30 focus:ring-2 focus:ring-amber-300/60">
                <kbd class="pointer-events-none absolute right-2.5 top-1/2 hidden -translate-y-1/2 rounded border border-paper-300 bg-paper-50 px-1.5 py-0.5 text-[10px] font-semibold text-ink-400 lg:block">/</kbd>
            </form>

            <div class="ml-auto flex items-center gap-0.5 sm:gap-1 md:ml-0">
                @auth
                    @if ($isCustomer)
                        <a href="{{ route('customer.wishlist.index') }}" class="relative flex h-9 w-9 items-center justify-center rounded-lg text-ink-600 transition hover:bg-ink-950/5 hover:text-rose-600" title="Wishlist">
                            <x-icon name="heart" class="h-[18px] w-[18px]" />
                            @if ($wishlistCount)
                                <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[9px] font-bold text-white">{{ $wishlistCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('customer.cart.index') }}" class="relative flex h-9 w-9 items-center justify-center rounded-lg text-ink-600 transition hover:bg-ink-950/5 hover:text-ink-950" title="Keranjang">
                            <x-icon name="shopping-cart" class="h-[18px] w-[18px]" />
                            @if ($cartCount)
                                <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-400 px-1 text-[9px] font-bold text-ink-950">{{ $cartCount }}</span>
                            @endif
                        </a>
                    @endif

                    <div x-data="notificationBell({{ $unreadCount }})" data-url="{{ route('notifications.unread') }}" class="relative">
                        <button type="button" @click="open = ! open" class="relative flex h-9 w-9 items-center justify-center rounded-lg text-ink-600 transition hover:bg-ink-950/5 hover:text-ink-950" aria-label="Notifikasi">
                            <x-icon name="bell" class="h-[18px] w-[18px]" />
                            <span x-show="count > 0" x-cloak class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[9px] font-bold text-white" x-text="count"></span>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-80 overflow-hidden rounded-xl border border-paper-300 bg-white shadow-[0_24px_60px_-30px_rgba(16,16,20,.45)]">
                            <div class="flex items-center justify-between border-b border-paper-200 px-4 py-3">
                                <p class="text-[12px] font-semibold uppercase tracking-[0.12em] text-ink-500">Notifikasi</p>
                                <a href="{{ route('notifications.index') }}" class="text-[11px] font-semibold text-amber-700 hover:text-amber-800">Lihat semua</a>
                            </div>
                            <template x-if="items.length === 0">
                                <p class="px-4 py-6 text-center text-[11px] text-ink-500">Belum ada notifikasi baru.</p>
                            </template>
                            <template x-for="item in items" :key="item.id">
                                <a :href="item.url" class="block border-b border-paper-200 px-4 py-3 transition last:border-0 hover:bg-paper-50">
                                    <p class="text-[12px] font-semibold text-ink-950" x-text="item.title"></p>
                                    <p class="mt-0.5 line-clamp-2 text-[11px] leading-snug text-ink-500" x-text="item.message"></p>
                                    <p class="mt-1 text-[10px] text-ink-400" x-text="item.time"></p>
                                </a>
                            </template>
                        </div>
                    </div>
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="hidden px-3 py-2 text-[13px] font-semibold text-ink-700 transition hover:text-ink-950 sm:inline-flex">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-accent btn-sm ml-1">Daftar</a>
                @else
                    <div x-data="{ open: false }" class="relative ml-1">
                        <button type="button" @click="open = ! open" class="flex items-center gap-2 rounded-lg border border-paper-300 bg-white p-1 pr-2 transition hover:border-ink-950/25">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-7 w-7 rounded-md object-cover">
                            <span class="hidden max-w-[100px] truncate text-[12px] font-semibold text-ink-900 sm:block">{{ $user->name }}</span>
                            <x-icon name="chevron-down" class="hidden h-3.5 w-3.5 text-ink-400 sm:block" />
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-paper-300 bg-white p-1.5 shadow-[0_24px_60px_-30px_rgba(16,16,20,.45)]">
                            <div class="border-b border-paper-200 px-3 py-2.5">
                                <p class="truncate text-[13px] font-semibold text-ink-950">{{ $user->name }}</p>
                                <p class="truncate text-[11px] text-ink-500">{{ $user->email }}</p>
                                <span class="badge mt-2 bg-amber-100 text-amber-800 ring-amber-700/20">{{ $user->role->label() }}</span>
                            </div>
                            <a href="{{ route($user->role->dashboardRoute()) }}" class="mt-1 block rounded-md px-3 py-2 text-[12.5px] font-medium text-ink-700 hover:bg-paper-50">Dashboard</a>
                            <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-[12.5px] font-medium text-ink-700 hover:bg-paper-50">Profil</a>
                            <a href="{{ route('notifications.index') }}" class="block rounded-md px-3 py-2 text-[12.5px] font-medium text-ink-700 hover:bg-paper-50">Notifikasi</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full rounded-md px-3 py-2 text-left text-[12.5px] font-medium text-rose-700 hover:bg-rose-50">Keluar</button>
                            </form>
                        </div>
                    </div>
                @endguest
            </div>
        </div>

        {{-- Category rail --}}
        <div class="border-t border-paper-200 bg-white/60">
            <div class="mx-auto flex max-w-7xl items-center gap-6 overflow-x-auto px-4 no-scrollbar sm:px-6 lg:px-8">
                <a href="{{ route('products.index') }}" class="flex h-10 shrink-0 items-center text-[12px] font-semibold uppercase tracking-[0.1em] text-ink-950">Semua</a>
                @foreach ($navCategories as $navCategory)
                    <a href="{{ route('products.index', ['category' => $navCategory->id]) }}"
                       class="flex h-10 shrink-0 items-center border-b-2 border-transparent text-[12.5px] font-medium text-ink-500 transition hover:border-ink-950 hover:text-ink-950 {{ (string) request('category') === (string) $navCategory->id ? 'border-ink-950 text-ink-950' : '' }}">
                        {{ $navCategory->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <div x-show="mobileOpen" x-cloak class="border-t border-paper-200 bg-white px-4 py-4 lg:hidden">
            <form action="{{ route('products.index') }}" method="GET" class="relative mb-3">
                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk…" class="w-full rounded-lg border border-paper-400 bg-paper-50 py-2.5 pl-10 pr-4 text-[13px]">
            </form>
            <div class="grid gap-0.5">
                <a href="{{ route('home') }}" class="rounded-md px-3 py-2 text-[13px] font-medium text-ink-700 hover:bg-paper-50">Beranda</a>
                <a href="{{ route('products.index') }}" class="rounded-md px-3 py-2 text-[13px] font-medium text-ink-700 hover:bg-paper-50">Katalog</a>
                <a href="{{ route('compare.index') }}" class="rounded-md px-3 py-2 text-[13px] font-medium text-ink-700 hover:bg-paper-50">Bandingkan {{ $compareCount ? '('.$compareCount.')' : '' }}</a>
                @guest
                    <a href="{{ route('partner.register') }}" class="rounded-md px-3 py-2 text-[13px] font-medium text-ink-700 hover:bg-paper-50">Jual di LensMart</a>
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-[13px] font-medium text-ink-700 hover:bg-paper-50">Masuk</a>
                @endguest
                @auth
                    <a href="{{ route('profile.edit') }}" class="rounded-md px-3 py-2 text-[13px] font-medium text-ink-700 hover:bg-paper-50">Profil</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">
        <div class="mx-auto max-w-7xl px-4 pt-5 sm:px-6 lg:px-8">
            <x-flash />
        </div>
        {{ $slot }}
    </main>

    <footer class="mt-24 border-t border-white/10 bg-ink-950 text-paper-300">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:grid-cols-2 sm:px-6 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr] lg:gap-12 lg:px-8">
            <div>
                <a href="{{ route('home') }}" class="flex items-center" aria-label="LensMart — beranda">
                    <x-brand-logo variant="light" :tagline="false" />
                </a>
                <p class="mt-5 max-w-xs text-[13px] leading-6 text-white/50">Marketplace kamera, lensa, dan aksesori fotografi dari toko terverifikasi di seluruh Indonesia.</p>
                <p class="mt-5 flex items-start gap-2 text-[11.5px] leading-5 text-white/40">
                    <x-icon name="map-pin" class="mt-0.5 h-3.5 w-3.5 shrink-0" /> {{ config('lensmart.store.address') }}
                </p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/35">Jelajahi</p>
                <ul class="mt-4 space-y-2.5 text-[13px]">
                    <li><a href="{{ route('products.index') }}" class="text-white/65 transition hover:text-amber-300">Semua produk</a></li>
                    <li><a href="{{ route('products.index', ['sort' => 'rating']) }}" class="text-white/65 transition hover:text-amber-300">Rating tertinggi</a></li>
                    <li><a href="{{ route('products.index', ['condition' => 'new']) }}" class="text-white/65 transition hover:text-amber-300">Produk baru</a></li>
                    <li><a href="{{ route('compare.index') }}" class="text-white/65 transition hover:text-amber-300">Bandingkan produk</a></li>
                </ul>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/35">Untuk Partner</p>
                <ul class="mt-4 space-y-2.5 text-[13px]">
                    <li><a href="{{ route('partner.register') }}" class="text-white/65 transition hover:text-amber-300">Buka toko</a></li>
                    <li><a href="{{ route('login') }}" class="text-white/65 transition hover:text-amber-300">Masuk dashboard</a></li>
                    <li><a href="{{ route('products.index') }}" class="text-white/65 transition hover:text-amber-300">Panduan verifikasi</a></li>
                </ul>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/35">Bantuan</p>
                <ul class="mt-4 space-y-2.5 text-[13px]">
                    <li class="flex items-center gap-2 text-white/65"><x-icon name="envelope" class="h-3.5 w-3.5 text-white/35" /> {{ config('lensmart.store.email') }}</li>
                    <li class="flex items-center gap-2 text-white/65"><x-icon name="phone" class="h-3.5 w-3.5 text-white/35" /> {{ config('lensmart.store.phone') }}</li>
                </ul>
                <p class="mt-5 border-l-2 border-amber-400/60 pl-3 text-[11px] leading-5 text-white/40">Lingkungan demo: seluruh pembayaran disimulasikan dan tidak melibatkan uang sungguhan.</p>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-[10.5px] uppercase tracking-[0.14em] text-white/30 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>&copy; {{ now()->year }} {{ config('app.name') }} — Dibuat untuk portofolio</p>
                <p>Laravel / Blade / Tailwind</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
    <x-confirm-dialog />
</body>
</html>
