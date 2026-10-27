<x-account-shell title="Alamat Pengiriman" subtitle="Kelola alamat tujuan untuk mempercepat proses checkout.">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="text-xs text-ink-500">{{ $addresses->count() }} alamat tersimpan</p>
        <a href="{{ route('customer.addresses.create') }}" class="btn-primary btn-sm">
            <x-icon name="plus" class="h-4 w-4" /> Tambah alamat
        </a>
    </div>

    @if ($addresses->isEmpty())
        <x-empty-state icon="map-pin" title="Belum ada alamat" description="Tambahkan alamat pengiriman agar Anda dapat melakukan checkout dengan cepat.">
            <a href="{{ route('customer.addresses.create') }}" class="btn-primary btn-sm">Tambah alamat pertama</a>
        </x-empty-state>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($addresses as $address)
                <article class="card flex flex-col p-5 {{ $address->is_default ? 'ring-2 ring-amber-400/70' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-600/10">
                                <x-icon name="map-pin" class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="text-sm font-bold text-ink-950">{{ $address->label }}</p>
                                @if ($address->is_default)
                                    <span class="badge mt-1 bg-amber-100 text-amber-800 ring-amber-600/20">Alamat utama</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('customer.addresses.edit', $address) }}" class="text-[11px] font-bold text-amber-600 hover:text-amber-700">Edit</a>
                    </div>

                    <div class="mt-4 flex-1 text-sm">
                        <p class="font-semibold text-ink-950">{{ $address->recipient_name }}</p>
                        <p class="mt-0.5 text-xs text-ink-500">{{ $address->phone }}</p>
                        <p class="mt-2 text-xs leading-relaxed text-ink-600">{{ $address->full_address }}</p>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-paper-200 pt-3">
                        @if (! $address->is_default)
                            <form method="POST" action="{{ route('customer.addresses.default', $address) }}">
                                @csrf
                                <button type="submit" class="btn-outline btn-sm">Jadikan utama</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}" class="ml-auto" x-confirm="{ title: 'Hapus alamat ini?', message: 'Alamat ini akan dihapus dari daftar alamat Anda.', confirm: 'Ya, hapus', tone: 'danger' }">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1 text-[11px] font-semibold text-ink-400 transition hover:text-rose-500">
                                <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6 rounded-2xl bg-sky-50 p-4 text-[11px] leading-relaxed text-sky-900 ring-1 ring-inset ring-sky-600/20">
            <p class="flex items-start gap-2">
                <x-icon name="information-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                <span>Alamat utama akan otomatis dipilih saat checkout. Anda tetap dapat memilih alamat lain di halaman checkout.</span>
            </p>
        </div>
    @endif
</x-account-shell>
