<x-layouts.portal title="Ulasan Pelanggan" subtitle="Pantau reputasi toko, balas ulasan, dan laporkan ulasan yang melanggar.">
    @php
        $averageRating = $reviews->avg('rating') ?? 0;
        $repliedCount = $reviews->getCollection()->whereNotNull('store_reply')->count();
    @endphp

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="stat-card">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <x-icon name="star" class="h-5 w-5" />
            </span>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-ink-950">{{ number_format($averageRating, 1, ',', '.') }}</p>
            <p class="text-xs font-semibold text-ink-500">Rata-rata rating</p>
        </div>
        <div class="stat-card">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                <x-icon name="chat" class="h-5 w-5" />
            </span>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-ink-950">{{ $reviews->total() }}</p>
            <p class="text-xs font-semibold text-ink-500">Total ulasan produk</p>
        </div>
        <div class="stat-card">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <x-icon name="check-circle" class="h-5 w-5" />
            </span>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-ink-950">{{ $repliedCount }}</p>
            <p class="text-xs font-semibold text-ink-500">Sudah dibalas (halaman ini)</p>
        </div>
    </div>

    <div class="mt-5 space-y-4">
        @forelse ($reviews as $review)
            @php
                $report = $review->reports->first();
            @endphp
            <article class="card overflow-hidden">
                <div class="flex flex-wrap items-start gap-3 border-b border-paper-200 bg-paper-50/60 px-5 py-4">
                    <img src="{{ $review->product?->image_url ?? asset('images/placeholder-product.svg') }}" alt="{{ $review->product?->name }}" class="h-12 w-12 rounded-xl object-cover ring-1 ring-paper-300">
                    <div class="min-w-0 flex-1">
                        <p class="line-clamp-1 text-sm font-extrabold text-ink-950">{{ $review->product?->name ?? 'Produk dihapus' }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <x-star-rating :rating="$review->rating" />
                            <span class="text-[11px] text-ink-400">{{ format_tanggal($review->created_at, true) }}</span>
                            @if ($review->is_hidden)
                                <span class="badge bg-rose-50 text-rose-700 ring-rose-600/20">Disembunyikan</span>
                            @endif
                        </div>
                    </div>
                    @if ($review->order)
                        <p class="text-[11px] text-ink-400">Pesanan {{ $review->order->order_number }}</p>
                    @endif
                </div>

                <div class="p-5">
                    <div class="flex items-start gap-3">
                        <img src="{{ $review->customer?->avatar_url ?? 'https://ui-avatars.com/api/?background=e2e8f0&color=334155&bold=true&name=U' }}" alt="{{ $review->customer?->name }}" class="h-9 w-9 rounded-full object-cover">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-ink-950">{{ $review->customer?->name ?? 'Pelanggan' }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-ink-600">{{ $review->comment ?: 'Pelanggan tidak menuliskan komentar.' }}</p>

                            @if ($review->images->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($review->images as $image)
                                        <a href="{{ $image->url }}" target="_blank" class="block h-16 w-16 overflow-hidden rounded-lg border border-paper-300">
                                            <img src="{{ $image->url }}" alt="Foto ulasan" class="h-full w-full object-cover">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($review->store_reply)
                        <div class="mt-4 rounded-xl bg-emerald-50/70 p-4 ring-1 ring-inset ring-emerald-600/10">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge bg-emerald-100 text-emerald-800 ring-emerald-600/20">
                                    <x-icon name="check-circle" class="h-3 w-3" /> Balasan toko
                                </span>
                                @if ($review->replied_at)
                                    <span class="text-[10px] text-ink-400">{{ format_tanggal($review->replied_at, true) }}</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm leading-relaxed text-ink-700">{{ $review->store_reply }}</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('partner.reviews.reply', $review) }}" class="mt-4">
                            @csrf
                            <label for="reply-{{ $review->id }}" class="label text-xs">Balas ulasan</label>
                            <textarea id="reply-{{ $review->id }}" name="store_reply" rows="3" maxlength="2000" required class="textarea !min-h-0 text-xs" placeholder="Terima kasih atas ulasan Anda…"></textarea>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <button type="submit" class="btn-primary btn-sm">
                                    <x-icon name="chat" class="h-3.5 w-3.5" /> Kirim balasan
                                </button>
                                <span class="text-[10px] text-ink-400">Balasan hanya dapat dikirim satu kali.</span>
                            </div>
                        </form>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-paper-200 pt-3">
                        @if ($report)
                            <span class="badge {{ $report->status === \App\Enums\ReviewReportStatus::Resolved ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">
                                <x-icon name="flag" class="h-3 w-3" /> Laporan {{ $report->status->label() }}
                            </span>
                            @if ($report->admin_response)
                                <p class="text-[11px] text-ink-500">Tanggapan admin: {{ $report->admin_response }}</p>
                            @endif
                        @else
                            <form method="POST" action="{{ route('partner.reviews.report', $review) }}" class="flex flex-1 flex-col gap-2 sm:flex-row" x-confirm="{ title: 'Laporkan ulasan ini?', message: 'Laporan akan dikirim ke admin untuk ditinjau.', confirm: 'Ya, laporkan', tone: 'warning' }">
                                @csrf
                                <input type="text" name="reason" required maxlength="1000" class="input !py-2 flex-1 text-xs" placeholder="Alasan laporan (mis. spam, ujaran kebencian)">
                                <button type="submit" class="btn-outline btn-sm shrink-0 text-rose-600 hover:bg-rose-50">
                                    <x-icon name="flag" class="h-3.5 w-3.5" /> Laporkan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <x-empty-state icon="chat" title="Belum ada ulasan" description="Ulasan pembeli akan muncul di sini setelah pesanan selesai dan pelanggan mengirim ulasan.">
                <a href="{{ route('partner.products.index') }}" class="btn-primary btn-sm">Kelola produk</a>
            </x-empty-state>
        @endforelse
    </div>

    <div class="mt-5">{{ $reviews->links() }}</div>
</x-layouts.portal>
