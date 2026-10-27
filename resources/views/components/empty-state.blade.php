@props(['icon' => 'photo', 'title' => 'Belum ada data', 'description' => null])

<div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-paper-400 bg-white/60 px-6 py-14 text-center">
    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-paper-100 text-ink-400">
        <x-icon :name="$icon" class="h-7 w-7" />
    </div>
    <h3 class="mt-4 text-base font-semibold text-ink-950">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-md text-sm text-ink-500">{{ $description }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
