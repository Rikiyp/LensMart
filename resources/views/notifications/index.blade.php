<x-layouts.public title="Notifikasi">
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="section-eyebrow">Pusat aktivitas</p>
                <h1 class="mt-2 font-display text-4xl font-semibold tracking-tight text-ink-950">Notifikasi</h1>
                <p class="mt-2 text-sm text-ink-500">Pembaruan pesanan, pembayaran, dan aktivitas akun Anda.</p>
            </div>
            @if ($notifications->isNotEmpty())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn-outline btn-sm">
                        <x-icon name="check-circle" class="h-3.5 w-3.5" /> Tandai semua dibaca
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-6 card overflow-hidden">
            @if ($notifications->isEmpty())
                <div class="p-6">
                    <x-empty-state icon="bell" title="Belum ada notifikasi" description="Notifikasi tentang pesanan dan akun Anda akan muncul di sini.">
                        <a href="{{ route('products.index') }}" class="btn-primary btn-sm">Jelajahi katalog</a>
                    </x-empty-state>
                </div>
            @else
                <ul class="divide-y divide-paper-200">
                    @foreach ($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $isUnread = $notification->read_at === null;
                        @endphp
                        <li>
                            <a href="{{ route('notifications.read', $notification->id) }}"
                               class="flex items-start gap-3 px-5 py-4 transition hover:bg-paper-50 {{ $isUnread ? 'bg-amber-50/40' : '' }}">
                                <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $isUnread ? 'bg-amber-100 text-amber-700' : 'bg-paper-100 text-ink-500' }}">
                                    <x-icon :name="$data['icon'] ?? 'bell'" class="h-5 w-5" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <p class="text-sm font-bold text-ink-950">{{ $data['title'] ?? 'Notifikasi' }}</p>
                                        <span class="text-[10px] text-ink-400">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-0.5 text-xs leading-relaxed text-ink-500">{{ $data['message'] ?? '' }}</p>
                                    @if (! empty($data['note']))
                                        <p class="mt-1 rounded-lg bg-paper-50 px-2.5 py-1.5 text-[11px] text-ink-500">{{ $data['note'] }}</p>
                                    @endif
                                </div>
                                @if ($isUnread)
                                    <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-amber-500" title="Belum dibaca"></span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mt-6">{{ $notifications->links() }}</div>
    </div>
</x-layouts.public>
