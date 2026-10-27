<x-layouts.portal :title="$store->name" subtitle="Detail partner, verifikasi toko, dan riwayat pencairan.">
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.partners.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-500 hover:text-amber-600">
            <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Semua partner
        </a>
        <span class="badge {{ $store->status->badge() }}">{{ $store->status->label() }}</span>
        <span class="text-xs text-ink-500">Bergabung {{ format_tanggal($store->created_at) }}</span>
        @if ($store->isVerified())
            <span class="flex items-center gap-1 text-xs font-semibold text-emerald-600"><x-icon name="shield-check" class="h-4 w-4" /> Terverifikasi</span>
        @endif
    </div>

    @if ($store->rejection_note)
        <div class="mb-5 flex items-start gap-3 rounded-2xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-inset ring-rose-600/20">
            <x-icon name="exclamation-circle" class="mt-0.5 h-5 w-5 shrink-0" />
            <div>
                <p class="font-bold">Catatan penolakan</p>
                <p class="mt-0.5 text-xs leading-relaxed">{{ $store->rejection_note }}</p>
            </div>
        </div>
    @endif

    <div class="grid gap-5 xl:grid-cols-[1fr_340px]">
        <div class="space-y-5">
            <section class="card overflow-hidden">
                <div class="flex items-center gap-4 border-b border-paper-200 px-5 py-5">
                    <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="h-16 w-16 rounded-2xl object-cover ring-1 ring-paper-300">
                    <div class="min-w-0">
                        <p class="text-lg font-extrabold text-ink-950">{{ $store->name }}</p>
                        <p class="text-xs text-ink-500">{{ $store->city ?? '-' }} · {{ $store->phone ?? '-' }}</p>
                        <p class="mt-1 text-[11px] text-ink-400">{{ $store->products->count() }} produk · {{ $store->orders_count }} pesanan</p>
                    </div>
                </div>
                <div class="grid gap-4 p-5 text-sm sm:grid-cols-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Pemilik akun</p>
                        <p class="mt-1 font-semibold text-ink-950">{{ $store->user?->name ?? '-' }}</p>
                        <p class="text-xs text-ink-500">{{ $store->user?->email }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Rekening pencairan</p>
                        <p class="mt-1 font-semibold text-ink-950">{{ $store->bank_name ?? '-' }} · {{ $store->account_number ?? '-' }}</p>
                        <p class="text-xs text-ink-500">{{ $store->account_holder ?? '-' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Deskripsi</p>
                        <p class="mt-1 text-xs leading-relaxed text-ink-600">{{ $store->description ?: 'Belum ada deskripsi.' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Alamat toko</p>
                        <p class="mt-1 text-xs leading-relaxed text-ink-600">{{ $store->address ?: 'Belum diisi.' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Dokumen legalitas</p>
                        @if ($store->legal_document)
                            <a href="{{ asset('storage/'.$store->legal_document) }}" target="_blank" class="mt-1 inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 hover:text-amber-700">
                                <x-icon name="document" class="h-3.5 w-3.5" /> Lihat dokumen
                            </a>
                        @else
                            <p class="mt-1 text-xs text-ink-400">Belum diunggah</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Status akun</p>
                        <p class="mt-1"><span class="badge {{ $store->user?->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20' }}">{{ $store->user?->is_active ? 'Aktif' : 'Nonaktif' }}</span></p>
                    </div>
                </div>
            </section>

            <section class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-paper-200 px-5 py-4">
                    <h2 class="text-sm font-extrabold text-ink-950">Produk toko</h2>
                    <a href="{{ route('admin.products.index', ['q' => $store->name]) }}" class="text-xs font-bold text-amber-600">Semua produk</a>
                </div>
                @if ($store->products->isEmpty())
                    <div class="p-5"><x-empty-state icon="cube" title="Belum ada produk" description="Partner ini belum menambahkan produk." /></div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Verifikasi</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($store->products as $product)
                                    <tr>
                                        <td class="min-w-[220px]">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-10 w-10 rounded-lg object-cover ring-1 ring-paper-300">
                                                <div class="min-w-0">
                                                    <p class="line-clamp-1 text-xs font-bold text-ink-950">{{ $product->name }}</p>
                                                    <p class="text-[10px] text-ink-400">{{ format_tanggal($product->created_at) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap text-xs font-semibold">{{ format_rupiah($product->price) }}</td>
                                        <td class="text-xs">{{ $product->stock }}</td>
                                        <td><span class="badge {{ $product->verification_status->badge() }}">{{ $product->verification_status->label() }}</span></td>
                                        <td class="text-right"><a href="{{ route('admin.products.index', ['q' => $product->name]) }}" class="btn-outline btn-sm">Tinjau</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="card overflow-hidden">
                <div class="border-b border-paper-200 px-5 py-4">
                    <h2 class="text-sm font-extrabold text-ink-950">Riwayat pencairan</h2>
                </div>
                @if ($store->payouts->isEmpty())
                    <p class="px-5 py-8 text-center text-xs text-ink-400">Belum ada pencairan untuk toko ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Dibayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($store->payouts as $payout)
                                    <tr>
                                        <td class="text-xs font-semibold">{{ $payout->order?->order_number ?? '-' }}</td>
                                        <td class="text-xs font-extrabold">{{ format_rupiah($payout->amount) }}</td>
                                        <td><span class="badge {{ $payout->status->badge() }}">{{ $payout->status->label() }}</span></td>
                                        <td class="text-xs text-ink-500">{{ $payout->paid_at ? format_tanggal($payout->paid_at, true) : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-5">
            <section class="card p-5">
                <h2 class="text-sm font-extrabold text-ink-950">Verifikasi toko</h2>
                @if ($store->status === \App\Enums\StoreStatus::Verified)
                    <p class="mt-2 text-xs leading-relaxed text-ink-500">Toko sudah terverifikasi. Produk yang disetujui dapat tayang di katalog publik.</p>
                @else
                    <p class="mt-2 text-xs leading-relaxed text-ink-500">Periksa kelengkapan profil dan dokumen legalitas sebelum menyetujui.</p>
                    <form method="POST" action="{{ route('admin.partners.verify', $store) }}" class="mt-4 space-y-2">
                        @csrf
                        <input type="hidden" name="action" value="verify">
                        <button type="submit" class="btn-primary w-full">
                            <x-icon name="check-circle" class="h-4 w-4" /> Setujui toko
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.partners.verify', $store) }}" class="mt-4 space-y-2 border-t border-paper-200 pt-4" x-confirm="{ title: 'Tolak verifikasi toko?', message: 'Toko akan ditandai ditolak dan produknya tidak tayang sampai diajukan ulang.', confirm: 'Ya, tolak toko', tone: 'warning' }">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <label for="rejection_note" class="label text-xs">Tolak dengan catatan</label>
                    <textarea id="rejection_note" name="rejection_note" rows="3" maxlength="1000" class="textarea !min-h-0 text-xs" placeholder="Jelaskan alasan penolakan…">{{ old('rejection_note') }}</textarea>
                    <x-input-error :messages="$errors->get('rejection_note')" class="mt-1" />
                    <button type="submit" class="btn-outline w-full text-rose-600 hover:bg-rose-50">
                        <x-icon name="x-mark" class="h-4 w-4" /> Tolak toko
                    </button>
                </form>
            </section>

            <section class="card p-5">
                <h2 class="text-sm font-extrabold text-ink-950">Ringkasan</h2>
                <dl class="mt-4 space-y-2.5 text-xs">
                    <div class="flex justify-between"><dt class="text-ink-500">Total produk</dt><dd class="font-semibold text-ink-950">{{ $store->products->count() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Total pesanan</dt><dd class="font-semibold text-ink-950">{{ $store->orders_count }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Total pencairan</dt><dd class="font-semibold text-ink-950">{{ format_rupiah($store->payouts->sum('amount')) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Status</dt><dd><span class="badge {{ $store->status->badge() }}">{{ $store->status->label() }}</span></dd></div>
                </dl>
            </section>

            <a href="{{ route('admin.partners.index') }}" class="btn-outline w-full">Kembali ke daftar</a>
        </aside>
    </div>
</x-layouts.portal>
