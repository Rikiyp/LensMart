<x-layouts.portal title="Moderasi Ulasan" subtitle="Kelola ulasan publik dan tanggapi laporan partner.">
    <div class="mb-5 flex flex-wrap gap-2">
        @foreach ([
            ['all', 'Semua Ulasan'],
            ['visible', 'Tampil'],
            ['hidden', 'Disembunyikan'],
        ] as $tab)
            @php
                [$value, $label] = $tab;
            @endphp
            <a href="{{ route('admin.reviews.index', ['visibility' => $value]) }}"
               class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ $visibility === $value ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.35fr_1fr]">
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-ink-950">Daftar ulasan</h2>
                    <p class="text-xs text-ink-500">{{ $reviews->total() }} ulasan · {{ $visibility === 'hidden' ? 'disembunyikan' : ($visibility === 'visible' ? 'tampil' : 'semua status') }}</p>
                </div>
            </div>

            @forelse ($reviews as $review)
                <article class="card p-5">
                    <div class="flex flex-wrap items-start gap-3">
                        <img src="{{ $review->product?->image_url ?? asset('images/placeholder-product.svg') }}" alt="{{ $review->product?->name }}" class="h-12 w-12 rounded-xl object-cover ring-1 ring-paper-300">
                        <div class="min-w-0 flex-1">
                            <p class="line-clamp-1 text-sm font-bold text-ink-950">{{ $review->product?->name ?? 'Produk dihapus' }}</p>
                            <p class="text-[11px] text-ink-500">{{ $review->product?->store?->name }} · {{ $review->customer?->name }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <x-star-rating :rating="$review->rating" />
                                <span class="text-[10px] text-ink-400">{{ format_tanggal($review->created_at, true) }}</span>
                                @if ($review->is_hidden)
                                    <span class="badge bg-rose-50 text-rose-700 ring-rose-600/20">Disembunyikan</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <form method="POST" action="{{ route('admin.reviews.toggle', $review) }}" x-confirm="{ title: '{{ $review->is_hidden ? 'Tampilkan ulasan ini?' : 'Sembunyikan ulasan ini?' }}', message: '{{ $review->is_hidden ? 'Ulasan akan tampil kembali di halaman produk.' : 'Ulasan tidak akan tampil di halaman produk sampai ditampilkan kembali.' }}', confirm: '{{ $review->is_hidden ? 'Ya, tampilkan' : 'Ya, sembunyikan' }}', tone: '{{ $review->is_hidden ? 'primary' : 'warning' }}' }">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-outline btn-sm">{{ $review->is_hidden ? 'Tampilkan' : 'Sembunyikan' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" x-confirm="{ title: 'Hapus ulasan permanen?', message: 'Ulasan akan dihapus dan tidak dapat dipulihkan.', confirm: 'Ya, hapus', tone: 'danger' }">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-full text-ink-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus ulasan">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    </div>

                    <p class="mt-3 text-sm leading-relaxed text-ink-600">{{ $review->comment ?: 'Tanpa komentar.' }}</p>

                    @if ($review->images->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($review->images as $image)
                                <a href="{{ $image->url }}" target="_blank" class="h-14 w-14 overflow-hidden rounded-lg border border-paper-300">
                                    <img src="{{ $image->url }}" alt="Foto ulasan" class="h-full w-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($review->store_reply)
                        <div class="mt-3 rounded-xl bg-emerald-50/70 p-3 text-xs leading-relaxed text-emerald-900">
                            <p class="font-bold">Balasan toko</p>
                            <p class="mt-1">{{ $review->store_reply }}</p>
                        </div>
                    @endif

                    @if ($review->reports->isNotEmpty())
                        <div class="mt-3 space-y-2">
                            @foreach ($review->reports as $report)
                                <div class="rounded-xl bg-amber-50 p-3 text-[11px] text-amber-900 ring-1 ring-inset ring-amber-600/20">
                                    <p class="font-bold">Dilaporkan oleh {{ $report->store?->name ?? 'toko' }}</p>
                                    <p class="mt-0.5 leading-relaxed">{{ $report->reason }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <x-empty-state icon="chat" title="Tidak ada ulasan" description="Belum ada ulasan pada filter ini." />
            @endforelse

            <div>{{ $reviews->links() }}</div>
        </section>

        <section class="space-y-4">
            <div>
                <h2 class="text-sm font-extrabold text-ink-950">Laporan partner</h2>
                <p class="text-xs text-ink-500">Tinjau dan beri tanggapan resmi.</p>
            </div>

            @forelse ($reports as $report)
                <article class="card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="badge {{ $report->status === \App\Enums\ReviewReportStatus::Resolved ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">
                            {{ $report->status->label() }}
                        </span>
                        <span class="text-[10px] text-ink-400">{{ format_tanggal($report->created_at, true) }}</span>
                    </div>
                    <p class="mt-2 text-xs font-bold text-ink-950">{{ $report->review?->product?->name ?? 'Produk' }}</p>
                    <p class="text-[11px] text-ink-500">Dilaporkan oleh {{ $report->store?->name }}</p>
                    <p class="mt-2 rounded-lg bg-paper-50 px-3 py-2 text-[11px] leading-relaxed text-ink-600">{{ $report->reason }}</p>

                    @if ($report->admin_response)
                        <div class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-[11px] leading-relaxed text-emerald-800">
                            <p class="font-bold">Tanggapan admin</p>
                            <p class="mt-0.5">{{ $report->admin_response }}</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.reviews.respond', $report) }}" class="mt-3 space-y-2">
                            @csrf
                            <textarea name="admin_response" rows="2" required maxlength="2000" class="textarea !min-h-0 text-[11px]" placeholder="Tanggapan untuk partner…"></textarea>
                            <button type="submit" class="btn-primary btn-sm w-full">Kirim tanggapan</button>
                        </form>
                    @endif
                </article>
            @empty
                <x-empty-state icon="flag" title="Tidak ada laporan" description="Laporan ulasan dari partner akan muncul di sini." />
            @endforelse

            <div>{{ $reports->links() }}</div>
        </section>
    </div>
</x-layouts.portal>
