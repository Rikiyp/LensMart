@props(['title' => null, 'subtitle' => null])

@php
    $user = auth()->user();
    $isPartner = $user->isPartner();
    $store = $isPartner ? $user->store : null;
    $unreadCount = $user->unreadNotifications()->count();

    if ($user->isAdmin()) {
        $portalLabel = 'Portal admin';
        $navGroups = [
            'Utama' => [
                ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'squares'],
                ['route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'label' => 'Pesanan', 'icon' => 'shopping-bag'],
                ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Produk', 'icon' => 'cube'],
                ['route' => 'admin.partners.index', 'pattern' => 'admin.partners.*', 'label' => 'Partner', 'icon' => 'store'],
                ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => 'Akun', 'icon' => 'users'],
            ],
            'Keuangan & Moderasi' => [
                ['route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'label' => 'Laporan', 'icon' => 'chart-bar'],
                ['route' => 'admin.payouts.index', 'pattern' => 'admin.payouts.*', 'label' => 'Pencairan', 'icon' => 'wallet'],
                ['route' => 'admin.reviews.index', 'pattern' => 'admin.reviews.*', 'label' => 'Ulasan', 'icon' => 'chat'],
            ],
            'Master Data' => [
                ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Kategori', 'icon' => 'tag'],
                ['route' => 'admin.brands.index', 'pattern' => 'admin.brands.*', 'label' => 'Merek', 'icon' => 'sparkles'],
                ['route' => 'admin.spec-attributes.index', 'pattern' => 'admin.spec-attributes.*', 'label' => 'Atribut Spesifikasi', 'icon' => 'clipboard'],
            ],
        ];
    } else {
        $portalLabel = 'Portal partner';
        $navGroups = [
            'Utama' => [
                ['route' => 'partner.dashboard', 'pattern' => 'partner.dashboard', 'label' => 'Dashboard', 'icon' => 'squares'],
                ['route' => 'partner.orders.index', 'pattern' => 'partner.orders.*', 'label' => 'Pesanan', 'icon' => 'shopping-bag'],
                ['route' => 'partner.products.index', 'pattern' => 'partner.products.*', 'label' => 'Produk', 'icon' => 'cube'],
            ],
            'Operasional' => [
                ['route' => 'partner.reviews.index', 'pattern' => 'partner.reviews.*', 'label' => 'Ulasan', 'icon' => 'chat'],
                ['route' => 'partner.finance.index', 'pattern' => 'partner.finance.*', 'label' => 'Keuangan', 'icon' => 'chart-bar'],
                ['route' => 'partner.store.edit', 'pattern' => 'partner.store.*', 'label' => 'Profil Toko', 'icon' => 'store'],
            ],
        ];
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#101014">
    <link rel="icon" href="{{ asset('images/lensmart-mark.svg') }}" type="image/svg+xml">
    <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-paper-100 font-sans text-ink-950 antialiased" x-data="{ sidebarOpen: false }">
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-ink-950/55 lg:hidden" @click="sidebarOpen = false"></div>

    <aside class="fixed inset-y-0 left-0 z-50 flex w-[264px] -translate-x-full flex-col bg-ink-950 text-white transition-transform duration-300 lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" x-cloak>
        <div class="flex h-[68px] items-center justify-between border-b border-white/10 px-5">
            <a href="{{ route('home') }}" class="flex items-center" aria-label="LensMart — beranda">
                <x-brand-logo variant="light" :tagline="false" />
            </a>
            <button type="button" @click="sidebarOpen = false" class="text-white/40 transition hover:text-white lg:hidden" aria-label="Tutup menu">
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        <div class="mx-3 mt-3 rounded-lg border border-white/10 bg-white/[0.04] p-3">
            <p class="text-[9.5px] font-semibold uppercase tracking-[0.18em] text-white/35">{{ $portalLabel }}</p>
            <div class="mt-2.5 flex items-center gap-2.5">
                @if ($isPartner)
                    <img src="{{ $store?->logo_url ?? $user->avatar_url }}" alt="{{ $store?->name ?? $user->name }}" class="h-9 w-9 rounded-md object-cover">
                @else
                    <span class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-400 text-[11px] font-bold text-ink-950">{{ $user->initials }}</span>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-[12.5px] font-semibold">{{ $isPartner ? $store?->name : $user->name }}</p>
                    <p class="truncate text-[10.5px] text-white/40">{{ $isPartner ? 'Partner toko' : 'Administrator' }}</p>
                </div>
            </div>
        </div>

        <nav class="mt-4 flex-1 space-y-5 overflow-y-auto px-3 pb-6">
            @foreach ($navGroups as $groupLabel => $items)
                <div>
                    <p class="px-3.5 text-[9.5px] font-semibold uppercase tracking-[0.18em] text-white/30">{{ $groupLabel }}</p>
                    <div class="mt-1.5 space-y-0.5">
                        @foreach ($items as $item)
                            <a href="{{ route($item['route']) }}"
                               class="sidebar-link {{ request()->routeIs($item['pattern']) ? 'sidebar-link-active' : '' }}">
                                <x-icon :name="$item['icon']" class="h-[17px] w-[17px]" />
                                <span>{{ $item['label'] }}</span>
                                @if (request()->routeIs($item['pattern']))
                                    <span class="ml-auto h-1 w-1 rounded-full bg-amber-400"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3">
            <a href="{{ $isPartner && $store ? route('products.index', ['q' => $store->name]) : route('home') }}" class="sidebar-link" target="_blank">
                <x-icon name="arrow-top-right" class="h-[17px] w-[17px]" />
                <span>{{ $isPartner ? 'Lihat toko publik' : 'Lihat marketplace' }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link w-full text-rose-300 hover:bg-rose-500/10 hover:text-rose-200">
                    <x-icon name="arrow-left" class="h-[17px] w-[17px]" />
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="lg:pl-[264px]">
        <header class="sticky top-0 z-30 border-b border-paper-300 bg-paper-100/90 backdrop-blur-md">
            <div class="flex h-[68px] items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button type="button" @click="sidebarOpen = true" class="-ml-2 flex h-9 w-9 items-center justify-center rounded-lg text-ink-600 hover:bg-ink-950/5 lg:hidden" aria-label="Buka menu">
                    <x-icon name="bars-3" class="h-5 w-5" />
                </button>

                <div class="min-w-0">
                    <p class="hidden text-[9.5px] font-semibold uppercase tracking-[0.18em] text-ink-400 sm:block">{{ $portalLabel }}</p>
                    <h1 class="truncate font-display text-[20px] font-semibold leading-tight tracking-[-0.02em] text-ink-950 sm:text-[23px]">{{ $title ?? 'Dashboard' }}</h1>
                    @if ($subtitle)
                        <p class="hidden truncate text-[11.5px] text-ink-500 sm:block">{{ $subtitle }}</p>
                    @endif
                </div>

                <div class="ml-auto flex items-center gap-0.5 sm:gap-1">
                    <div x-data="notificationBell({{ $unreadCount }})" data-url="{{ route('notifications.unread') }}" class="relative">
                        <button type="button" @click="open = ! open" class="relative flex h-9 w-9 items-center justify-center rounded-lg text-ink-600 transition hover:bg-ink-950/5 hover:text-ink-950" aria-label="Notifikasi">
                            <x-icon name="bell" class="h-[18px] w-[18px]" />
                            <span x-show="count > 0" x-cloak class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[9px] font-bold text-white" x-text="count"></span>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-80 overflow-hidden rounded-xl border border-paper-300 bg-white shadow-[0_24px_60px_-30px_rgba(16,16,20,.45)]">
                            <div class="flex items-center justify-between border-b border-paper-200 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-ink-500">Notifikasi</p>
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

                    <a href="{{ route('profile.edit') }}" class="ml-1 flex items-center gap-2 rounded-lg border border-paper-300 bg-white p-1 pr-2 transition hover:border-ink-950/25">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-7 w-7 rounded-md object-cover">
                        <span class="hidden max-w-[120px] truncate text-[12px] font-semibold text-ink-900 sm:block">{{ $user->name }}</span>
                        <x-icon name="chevron-down" class="hidden h-3.5 w-3.5 text-ink-400 sm:block" />
                    </a>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto max-w-7xl">
                @if ($isPartner && $store && ! $store->isVerified())
                    <div class="mb-5 flex flex-col gap-3 rounded-lg border border-amber-300/70 bg-amber-50 px-4 py-3 text-[12.5px] text-amber-900 sm:flex-row sm:items-center">
                        <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                        <p class="flex-1">
                            @if ($store->status === \App\Enums\StoreStatus::Rejected)
                                Toko Anda ditolak. Perbaiki profil lalu ajukan verifikasi ulang.
                            @else
                                Toko Anda masih menunggu verifikasi admin. Anda belum bisa menambah produk.
                            @endif
                        </p>
                        <a href="{{ route('partner.store.edit') }}" class="btn-outline btn-sm shrink-0">Lengkapi profil</a>
                    </div>
                @endif

                <x-flash />
                {{ $slot }}
            </div>
        </main>
    </div>

    @stack('scripts')
    <x-confirm-dialog />
</body>
</html>
