@props(['slug', 'name' => null, 'height' => null])

@php
    $heights = [
        'sony' => 20,
        'canon' => 21,
        'nikon' => 21,
        'fujifilm' => 19,
        'panasonic' => 19,
        'dji' => 26,
        'godox' => 21,
        'sigma' => 20,
    ];

    $brandHeight = (int) ($height ?? $heights[$slug] ?? 20);
@endphp

<img src="{{ asset('images/brands/'.$slug.'.svg') }}"
     alt="{{ $name ?? $slug }}"
     class="brand-mark"
     style="--brand-h: {{ $brandHeight }}px"
     decoding="async">
