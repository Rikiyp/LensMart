@props(['rating' => 0, 'count' => null, 'size' => 'h-3.5 w-3.5'])

@php
    $rating = (float) $rating;
    $filled = (int) round($rating);
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-1.5']) }}>
    <div class="flex items-center text-amber-500">
        @for ($i = 1; $i <= 5; $i++)
            <x-icon name="star" :class="$size.' '.($i <= $filled ? 'text-amber-500' : 'text-paper-400')" />
        @endfor
    </div>
    <span class="tabular text-[11px] font-semibold text-ink-700">{{ number_format($rating, 1, ',', '.') }}</span>
    @if (! is_null($count))
        <span class="tabular text-[11px] text-ink-400">({{ $count }})</span>
    @endif
</div>
