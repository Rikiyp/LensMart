@props(['slug' => '', 'class' => 'h-6 w-6'])

{{--
    Duotone category glyphs.
    Palette: amber-400 (main), amber-100 (highlight), ink (negative detail).
    Drawn as filled silhouettes so they stay readable at 28px.
--}}
<svg viewBox="0 0 24 24" fill="none"
     {{ $attributes->merge(['class' => $class]) }} aria-hidden="true">
    @switch($slug)
        @case('kamera-mirrorless')
            <path d="M5.4 6.3V4.9a1.3 1.3 0 0 1 1.3-1.3h4a1.3 1.3 0 0 1 1.3 1.3v1.4Z" fill="#FBBF24" />
            <rect x="16.3" y="4.2" width="2.8" height="1.9" rx="0.95" fill="#FDE68A" />
            <rect x="2.5" y="6.3" width="19" height="13.2" rx="2.9" fill="#FBBF24" />
            <circle cx="11.5" cy="12.9" r="4.3" fill="#FEF3C7" />
            <circle cx="11.5" cy="12.9" r="2.8" fill="#111827" opacity=".9" />
            <circle cx="11.5" cy="12.9" r="1.2" fill="#FBBF24" />
            <circle cx="17.6" cy="9.7" r="0.9" fill="#FEF3C7" opacity=".85" />
            <circle cx="10.2" cy="11.4" r="0.75" fill="#FFFFFF" opacity=".7" />
            @break

        @case('kamera-dslr')
            <path d="M6.9 8 8.8 4.1h6.4L17.1 8Z" fill="#FBBF24" />
            <rect x="16.4" y="9.5" width="2.9" height="1.7" rx="0.6" fill="#FDE68A" />
            <rect x="2.4" y="8" width="19.2" height="11.6" rx="2.5" fill="#FBBF24" />
            <circle cx="11.2" cy="13.8" r="4.5" fill="#FEF3C7" />
            <circle cx="11.2" cy="13.8" r="2.9" fill="#111827" opacity=".9" />
            <circle cx="11.2" cy="13.8" r="1.25" fill="#FBBF24" />
            <circle cx="17.7" cy="12.6" r="0.9" fill="#FEF3C7" opacity=".85" />
            <circle cx="9.8" cy="12.2" r="0.8" fill="#FFFFFF" opacity=".7" />
            @break

        @case('kamera-instan')
            <rect x="3.6" y="4.4" width="16.8" height="16.2" rx="3.3" fill="#FBBF24" />
            <rect x="5.8" y="6.4" width="3.6" height="2.7" rx="0.7" fill="#FDE68A" />
            <rect x="14.6" y="6.4" width="3.8" height="2.7" rx="0.9" fill="#FEF3C7" />
            <rect x="7.1" y="16.9" width="9.8" height="2" rx="1" fill="#FEF3C7" />
            <circle cx="12" cy="12" r="3.7" fill="#FEF3C7" />
            <circle cx="12" cy="12" r="2.3" fill="#111827" opacity=".9" />
            <circle cx="12" cy="12" r="1" fill="#FBBF24" />
            <circle cx="10.7" cy="10.7" r="0.7" fill="#FFFFFF" opacity=".7" />
            @break

        @case('kamera-action')
            <rect x="5" y="3" width="14" height="14.8" rx="3.7" fill="#FBBF24" />
            <rect x="7.1" y="14.3" width="2.7" height="1.5" rx="0.6" fill="#FDE68A" />
            <path d="M8.6 17.8h6.8l1 3.4H7.6Z" fill="#FBBF24" />
            <circle cx="12" cy="9.6" r="3.8" fill="#FEF3C7" />
            <circle cx="12" cy="9.6" r="2.3" fill="#111827" opacity=".9" />
            <circle cx="12" cy="9.6" r="1" fill="#FBBF24" />
            <circle cx="10.7" cy="8.3" r="0.7" fill="#FFFFFF" opacity=".7" />
            @break

        @case('lensa')
            <rect x="8.5" y="1.2" width="7" height="2.6" rx="0.8" fill="#FBBF24" />
            <rect x="6.2" y="3.2" width="11.6" height="17.8" rx="2.6" fill="#FBBF24" />
            <rect x="6.7" y="7" width="10.6" height="2.4" rx="0.5" fill="#FDE68A" />
            <circle cx="12" cy="14.1" r="3.4" fill="#FEF3C7" />
            <circle cx="12" cy="14.1" r="2.1" fill="#111827" opacity=".9" />
            <circle cx="12" cy="14.1" r="0.95" fill="#FBBF24" />
            <circle cx="10.9" cy="12.9" r="0.65" fill="#FFFFFF" opacity=".7" />
            @break

        @case('aksesori')
            <path d="M5.4 3.5a1.2 1.2 0 0 1 1.2-1.2h7.8l4.2 4.2v14.2a1.2 1.2 0 0 1-1.2 1.2H6.6a1.2 1.2 0 0 1-1.2-1.2Z" fill="#FBBF24" />
            <path d="M14.4 2.3v4.2h4.2Z" fill="#FEF3C7" />
            <g fill="#111827" opacity=".55">
                <rect x="8.2" y="8.8" width="1.5" height="3.8" rx="0.5" />
                <rect x="10.6" y="8.8" width="1.5" height="3.8" rx="0.5" />
                <rect x="13" y="8.8" width="1.5" height="3.8" rx="0.5" />
            </g>
            <rect x="8.2" y="16.4" width="5" height="1.5" rx="0.75" fill="#FEF3C7" opacity=".8" />
            @break

        @case('lighting-studio')
            <rect x="2.2" y="2.3" width="13.6" height="10" rx="2.2" fill="#FBBF24" />
            <rect x="4.2" y="4.3" width="9.6" height="6" rx="1.2" fill="#FEF3C7" />
            <g stroke="#FDE68A" stroke-width="1.9" stroke-linecap="round" opacity=".85">
                <path d="M18.2 2.6 20.6 1.2" />
                <path d="M18.2 7.3h3.1" />
                <path d="M18.2 12l2.4 1.4" />
            </g>
            <g stroke="#FBBF24" stroke-width="1.9" stroke-linecap="round">
                <path d="M9.8 12.3v6.6" />
                <path d="M9.8 12.3 6 21" />
                <path d="M9.8 12.3l3.8 8.7" />
                <path d="M4.8 21h10" />
            </g>
            @break

        @case('tripod-stabilizer')
            <rect x="7" y="2.3" width="10" height="3.7" rx="1.3" fill="#FBBF24" />
            <circle cx="12" cy="7.7" r="1.8" fill="#FEF3C7" />
            <g stroke="#FBBF24" stroke-width="2" stroke-linecap="round">
                <path d="M12 9.5v2.9" />
                <path d="M12 12.4 6.4 21" />
                <path d="M12 12.4 17.6 21" />
                <path d="M12 12.4V21" />
            </g>
            @break

        @case('tas-case')
            <path d="M8.4 8.2V6.6a3.6 3.6 0 0 1 7.2 0v1.6" stroke="#FBBF24" stroke-width="1.9" stroke-linecap="round" />
            <rect x="2.6" y="8" width="18.8" height="12.2" rx="3.4" fill="#FBBF24" />
            <path d="M2.6 11.3h18.8v2.5H2.6Z" fill="#FDE68A" />
            <rect x="9.6" y="13.8" width="4.8" height="3.3" rx="1" fill="#111827" opacity=".55" />
            @break

        @case('drone')
            <g stroke="#FBBF24" stroke-width="1.9" stroke-linecap="round">
                <path d="M9.2 10.1 5.7 6.6" />
                <path d="M14.8 10.1l3.5-3.5" />
                <path d="M9.2 13.9 5.7 17.4" />
                <path d="M14.8 13.9l3.5 3.5" />
            </g>
            <circle cx="4.6" cy="5.5" r="2.5" fill="#FEF3C7" />
            <circle cx="19.4" cy="5.5" r="2.5" fill="#FEF3C7" />
            <circle cx="4.6" cy="18.5" r="2.5" fill="#FEF3C7" />
            <circle cx="19.4" cy="18.5" r="2.5" fill="#FEF3C7" />
            <rect x="8.6" y="9.3" width="6.8" height="5.4" rx="2.1" fill="#FBBF24" />
            <circle cx="12" cy="16.9" r="1.7" fill="#111827" opacity=".75" />
            <circle cx="12" cy="12" r="1.1" fill="#FEF3C7" opacity=".9" />
            @break

        @case('audio')
            <rect x="8.6" y="1.8" width="6.8" height="10.6" rx="3.4" fill="#FBBF24" />
            <g stroke="#111827" stroke-width="1" stroke-linecap="round" opacity=".45">
                <path d="M10.4 5.1h3.2" />
                <path d="M10.4 7.6h3.2" />
            </g>
            <path d="M5.9 9.7a6.1 6.1 0 0 0 12.2 0" stroke="#FEF3C7" stroke-width="1.9" stroke-linecap="round" />
            <g stroke="#FBBF24" stroke-width="1.9" stroke-linecap="round">
                <path d="M12 16.4v3.4" />
                <path d="M8.8 20.8h6.4" />
            </g>
            @break

        @default
            <rect x="2.5" y="6.3" width="19" height="13.2" rx="2.9" fill="#FBBF24" />
            <circle cx="11.5" cy="12.9" r="4.3" fill="#FEF3C7" />
            <circle cx="11.5" cy="12.9" r="2.8" fill="#111827" opacity=".9" />
    @endswitch
</svg>
