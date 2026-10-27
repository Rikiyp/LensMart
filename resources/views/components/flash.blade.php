@php
    $messages = [
        'success' => ['bg-emerald-50 text-emerald-800 ring-emerald-600/20', 'check-circle'],
        'error' => ['bg-rose-50 text-rose-800 ring-rose-600/20', 'exclamation-circle'],
        'warning' => ['bg-amber-50 text-amber-900 ring-amber-600/20', 'exclamation-triangle'],
        'info' => ['bg-sky-50 text-sky-800 ring-sky-600/20', 'information-circle'],
    ];
@endphp

@foreach ($messages as $type => [$classes, $icon])
    @if (session($type))
        <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-start gap-3 rounded-2xl px-4 py-3 text-sm ring-1 ring-inset {{ $classes }}">
            <x-icon :name="$icon" class="mt-0.5 h-5 w-5 shrink-0" />
            <p class="flex-1 font-medium">{{ session($type) }}</p>
            <button type="button" @click="show = false" class="shrink-0 opacity-60 transition hover:opacity-100" aria-label="Tutup">
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-start gap-3 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-inset ring-rose-600/20">
        <x-icon name="exclamation-circle" class="mt-0.5 h-5 w-5 shrink-0" />
        <div class="flex-1">
            <p class="font-semibold">Periksa kembali input Anda.</p>
            <ul class="mt-1 list-inside list-disc space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" @click="show = false" class="shrink-0 opacity-60 transition hover:opacity-100" aria-label="Tutup">
            <x-icon name="x-mark" class="h-4 w-4" />
        </button>
    </div>
@endif
