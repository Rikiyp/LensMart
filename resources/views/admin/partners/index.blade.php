<x-layouts.portal title="Partner Toko" subtitle="Verifikasi toko dan pantau performa partner LensMart.">
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.partners.index') }}"
           class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ ! $status ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
            Semua
        </a>
        @foreach ($statuses as $item)
            <a href="{{ route('admin.partners.index', ['status' => $item->value]) }}"
               class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ $status === $item->value ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
                {{ $item->label() }}
            </a>
        @endforeach
    </div>

    @if ($stores->isEmpty())
        <x-empty-state icon="store" title="Tidak ada toko" description="Belum ada partner toko yang sesuai dengan filter ini." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($stores as $store)
                <article class="card flex flex-col overflow-hidden">
                    <div class="flex items-start gap-3 border-b border-paper-200 bg-paper-50/60 px-5 py-4">
                        <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="h-12 w-12 rounded-xl object-cover ring-1 ring-paper-300">
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('admin.partners.show', $store) }}" class="line-clamp-1 text-sm font-extrabold text-ink-950 hover:text-amber-600">{{ $store->name }}</a>
                            <p class="mt-0.5 truncate text-[11px] text-ink-400">{{ $store->city ?? 'Kota belum diisi' }}</p>
                            <span class="badge mt-1.5 {{ $store->status->badge() }}">{{ $store->status->label() }}</span>
                        </div>
                    </div>
                    <div class="flex-1 p-5">
                        <p class="line-clamp-2 text-xs leading-relaxed text-ink-500">{{ $store->description ?: 'Belum ada deskripsi toko.' }}</p>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <dt class="text-[10px] uppercase tracking-wider text-ink-400">Produk</dt>
                                <dd class="mt-0.5 text-base font-extrabold text-ink-950">{{ $store->products_count }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] uppercase tracking-wider text-ink-400">Pemilik</dt>
                                <dd class="mt-0.5 truncate font-semibold text-ink-900">{{ $store->user?->name ?? '-' }}</dd>
                            </div>
                        </dl>
                        <div class="mt-4 rounded-xl bg-paper-50 px-3 py-2 text-[11px] text-ink-500">
                            <p class="truncate">{{ $store->user?->email }}</p>
                            <p class="mt-0.5">{{ $store->phone ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2 border-t border-paper-200 p-4">
                        <a href="{{ route('admin.partners.show', $store) }}" class="btn-outline btn-sm flex-1">Detail</a>
                        @if ($store->status === \App\Enums\StoreStatus::Pending)
                            <form method="POST" action="{{ route('admin.partners.verify', $store) }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="action" value="verify">
                                <button type="submit" class="btn-primary btn-sm w-full">Verifikasi</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-5">{{ $stores->links() }}</div>
    @endif
</x-layouts.portal>
