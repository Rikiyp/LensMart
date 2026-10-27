<x-layouts.portal title="Moderasi Produk" subtitle="Tinjau produk partner sebelum tayang di katalog publik.">
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.products.index', ['q' => $search]) }}"
           class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ ! $verification ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
            Semua
        </a>
        @foreach ($verifications as $item)
            <a href="{{ route('admin.products.index', ['verification' => $item->value, 'q' => $search]) }}"
               class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ $verification === $item->value ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
                {{ $item->label() }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="card mb-5 flex flex-col gap-2 p-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama produk…" class="input !py-2 pl-10 text-xs">
        </div>
        <select name="verification" class="select !py-2 text-xs sm:w-48">
            <option value="">Semua verifikasi</option>
            @foreach ($verifications as $item)
                <option value="{{ $item->value }}" @selected($verification === $item->value)>{{ $item->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary btn-sm">Filter</button>
        @if ($search || $verification)
            <a href="{{ route('admin.products.index') }}" class="btn-ghost btn-sm justify-center">Reset</a>
        @endif
    </form>

    @if ($products->isEmpty())
        <x-empty-state icon="cube" title="Tidak ada produk" description="Tidak ada produk yang sesuai dengan filter ini." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($products as $product)
                <article class="card flex flex-col overflow-hidden">
                    <div class="relative aspect-[4/3] bg-paper-100">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        <span class="absolute left-3 top-3 badge {{ $product->verification_status->badge() }}">{{ $product->verification_status->label() }}</span>
                        <span class="absolute right-3 top-3 badge {{ $product->condition->badge() }}">{{ $product->condition->label() }}</span>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">{{ $product->brand?->name }} · {{ $product->category?->name }}</p>
                        <h2 class="mt-1 line-clamp-2 text-sm font-extrabold leading-snug text-ink-950">{{ $product->name }}</h2>
                        <p class="mt-1 text-[11px] text-ink-400">Model: {{ $product->model_name }}</p>
                        <p class="mt-2 flex items-center gap-1 text-xs text-ink-500"><x-icon name="store" class="h-3.5 w-3.5" /> {{ $product->store?->name }}</p>

                        <div class="mt-3 flex items-end justify-between">
                            <div>
                                <p class="text-lg font-extrabold text-ink-950">{{ format_rupiah($product->price) }}</p>
                                <p class="text-[10px] text-ink-400">Stok {{ $product->stock }}</p>
                            </div>
                            <span class="badge {{ $product->status->badge() }}">{{ $product->status->label() }}</span>
                        </div>

                        @if ($product->rejection_note)
                            <p class="mt-3 rounded-lg bg-rose-50 px-2.5 py-1.5 text-[10px] leading-relaxed text-rose-700">{{ $product->rejection_note }}</p>
                        @endif

                        <div class="mt-auto pt-4">
                            <div class="flex gap-2">
                                @if ($product->verification_status !== \App\Enums\VerificationStatus::Approved)
                                    <form method="POST" action="{{ route('admin.products.verify', $product) }}" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn-primary btn-sm w-full">
                                            <x-icon name="check" class="h-3.5 w-3.5" /> Setujui
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('products.show', $product) }}" target="_blank" class="btn-outline btn-sm justify-center">Lihat</a>
                            </div>

                            @if ($product->verification_status !== \App\Enums\VerificationStatus::Rejected)
                                <details class="mt-2 group">
                                    <summary class="cursor-pointer list-none text-center text-[11px] font-bold text-rose-600 hover:text-rose-700">Tolak produk</summary>
                                    <form method="POST" action="{{ route('admin.products.verify', $product) }}" class="mt-2 space-y-2 rounded-xl bg-rose-50/60 p-3" x-confirm="{ title: 'Tolak produk ini?', message: 'Produk akan ditandai ditolak dan tidak tampil di katalog publik.', confirm: 'Ya, tolak', tone: 'warning' }">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <textarea name="rejection_note" rows="2" required maxlength="1000" class="textarea !min-h-0 text-[11px]" placeholder="Alasan penolakan…"></textarea>
                                        <button type="submit" class="btn-danger btn-sm w-full"><x-icon name="x-mark" class="h-3.5 w-3.5" /> Kirim penolakan</button>
                                    </form>
                                </details>
                            @endif

                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="mt-2" x-confirm="{ title: 'Hapus produk dari katalog?', message: 'Produk akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.', confirm: 'Ya, hapus', tone: 'danger' }">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-center text-[10px] font-semibold text-ink-400 hover:text-rose-600">Hapus produk</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-5">{{ $products->links() }}</div>
    @endif
</x-layouts.portal>
