@props(['variant' => 'dark', 'tagline' => true, 'size' => 'md'])

@php
    $isLight = $variant === 'light';
    $markSize = $size === 'lg' ? 'h-12 w-12' : 'h-10 w-10';
    $wordSize = $size === 'lg' ? 'text-[27px]' : 'text-[22px]';
    $tagSize = $size === 'lg' ? 'text-[10px]' : 'text-[8px]';
    $wordClass = $isLight ? 'text-white' : 'text-ink-950';
    $tagClass = $isLight ? 'text-ink-400' : 'text-ink-500';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="relative flex {{ $markSize }} shrink-0 items-center justify-center overflow-hidden rounded-[14px] bg-gradient-to-br from-amber-300 via-amber-400 to-amber-500 shadow-[0_10px_24px_-12px_rgba(245,158,11,0.8)] ring-1 ring-amber-500/40">
        <svg viewBox="0 0 48 48" class="h-full w-full" fill="none" aria-hidden="true">
            <circle cx="24" cy="24" r="13.25" stroke="#0B1220" stroke-width="1.8" opacity=".9" />
            <g stroke="#0B1220" stroke-width="2.1" stroke-linecap="round" opacity=".95">
                <path d="M37.25 24 27.38 29.85" />
                <path d="M30.62 35.47 20.62 29.85" />
                <path d="M17.38 35.47 17.25 24" />
                <path d="M10.75 24 20.62 18.15" />
                <path d="M17.37 12.53 27.38 18.15" />
                <path d="M30.62 12.53 30.75 24" />
            </g>
            <path d="M30.75 24 27.38 18.15 20.62 18.15 17.25 24 20.62 29.85 27.38 29.85Z" stroke="#0B1220" stroke-width="1.15" stroke-linejoin="round" opacity=".3" />
            <path d="M14.4 17.6a13.1 13.1 0 0 1 7.5-5.7" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" opacity=".45" />
        </svg>
    </span>
    <span class="min-w-0 leading-none">
        <span class="block whitespace-nowrap font-display {{ $wordSize }} font-semibold tracking-[-0.03em] {{ $wordClass }}">
            Lens<span class="text-amber-500">Mart</span>
        </span>
        @if ($tagline)
            <span class="mt-1.5 hidden whitespace-nowrap {{ $tagSize }} font-bold uppercase tracking-[0.24em] {{ $tagClass }} sm:block">
                Marketplace Fotografi
            </span>
        @endif
    </span>
</span>
