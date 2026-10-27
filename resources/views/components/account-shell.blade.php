@props(['title' => 'Akun Saya', 'subtitle' => null])

@php
    $user = auth()->user();
    $accountNav = [
        ['route' => 'customer.dashboard', 'pattern' => 'customer.dashboard', 'label' => 'Ringkasan', 'icon' => 'squares'],
        ['route' => 'customer.orders.index', 'pattern' => 'customer.orders.*', 'label' => 'Pesanan Saya', 'icon' => 'shopping-bag'],
        ['route' => 'customer.wishlist.index', 'pattern' => 'customer.wishlist.*', 'label' => 'Wishlist', 'icon' => 'heart'],
        ['route' => 'customer.addresses.index', 'pattern' => 'customer.addresses.*', 'label' => 'Alamat', 'icon' => 'map-pin'],
        ['route' => 'notifications.index', 'pattern' => 'notifications.*', 'label' => 'Notifikasi', 'icon' => 'bell'],
        ['route' => 'profile.edit', 'pattern' => 'profile.*', 'label' => 'Profil', 'icon' => 'user-circle'],
    ];
@endphp

<x-layouts.public :title="$title">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
            <aside class="space-y-4">
                <div class="card p-5">
                    <div class="flex items-center gap-3">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-11 w-11 rounded-lg object-cover">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-extrabold text-ink-950">{{ $user->name }}</p>
                            <p class="truncate text-xs text-ink-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-paper-200 pt-3 text-xs">
                        <span class="text-ink-500">Status akun</span>
                        <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20">Aktif</span>
                    </div>
                </div>

                <nav class="card overflow-hidden p-2">
                    @foreach ($accountNav as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs($item['pattern']) ? 'bg-ink-950 text-white' : 'text-ink-600 hover:bg-paper-50 hover:text-ink-950' }}">
                            <x-icon :name="$item['icon']" class="h-[18px] w-[18px]" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <a href="{{ route('products.index') }}" class="btn-outline w-full">
                    <x-icon name="search" class="h-4 w-4" /> Lanjut belanja
                </a>
            </aside>

            <section class="min-w-0">
                <div class="mb-6">
                    <p class="section-eyebrow">Akun pelanggan</p>
                    <h1 class="mt-3 font-display text-[30px] leading-none font-semibold tracking-tight text-ink-950">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="mt-2 text-[12.5px] text-ink-500">{{ $subtitle }}</p>
                    @endif
                </div>
                {{ $slot }}
            </section>
        </div>
    </div>
</x-layouts.public>
