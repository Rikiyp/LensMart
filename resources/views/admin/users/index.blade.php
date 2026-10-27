<x-layouts.portal title="Manajemen Akun" subtitle="Kelola status aktif pelanggan dan partner toko.">
    @php
        $roleTabs = collect($roles)->map(fn ($item) => ['value' => $item->value, 'label' => $item->label()])->all();
    @endphp

    <form method="GET" action="{{ route('admin.users.index') }}" class="card mb-5 flex flex-col gap-2 p-3 sm:flex-row sm:items-center">
        <div class="flex flex-wrap gap-2">
            @foreach ($roleTabs as $tab)
                <a href="{{ route('admin.users.index', ['role' => $tab['value'], 'q' => $search]) }}"
                   class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ $role === $tab['value'] ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
        <div class="relative flex-1 sm:max-w-xs sm:ml-auto">
            <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama atau email…" class="input !py-2 pl-10 text-xs">
            <input type="hidden" name="role" value="{{ $role }}">
        </div>
        <button type="submit" class="btn-primary btn-sm">Cari</button>
        @if ($search)
            <a href="{{ route('admin.users.index', ['role' => $role]) }}" class="btn-ghost btn-sm justify-center">Reset</a>
        @endif
    </form>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <p class="text-xs text-ink-500">{{ $users->total() }} akun {{ strtolower(collect($roleTabs)->firstWhere('value', $role)['label'] ?? '') }}</p>
        <span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $users->count() }} di halaman ini</span>
    </div>

    @if ($users->isEmpty())
        <x-empty-state icon="users" title="Tidak ada akun ditemukan" description="Coba ubah kata kunci atau filter peran." />
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Akun</th>
                            <th>Peran</th>
                            <th>Toko</th>
                            <th>Terdaftar</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="min-w-[220px]">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full object-cover ring-1 ring-paper-300">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-ink-950">{{ $user->name }}</p>
                                            <p class="truncate text-[11px] text-ink-400">{{ $user->email }}</p>
                                            @if ($user->phone)
                                                <p class="text-[10px] text-ink-400">{{ $user->phone }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge {{ $user->isPartner() ? 'bg-violet-50 text-violet-700 ring-violet-600/20' : 'bg-sky-50 text-sky-700 ring-sky-600/20' }}">{{ $user->role->label() }}</span></td>
                                <td class="text-xs text-ink-600">
                                    @if ($user->store)
                                        <a href="{{ route('admin.partners.show', $user->store) }}" class="font-semibold text-ink-950 hover:text-amber-600">{{ $user->store->name }}</a>
                                        <p class="mt-0.5"><span class="badge {{ $user->store->status->badge() }}">{{ $user->store->status->label() }}</span></p>
                                    @else
                                        <span class="text-ink-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-xs text-ink-500">{{ format_tanggal($user->created_at) }}</td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" x-confirm="{ title: '{{ $user->is_active ? 'Nonaktifkan akun ini?' : 'Aktifkan akun ini?' }}', message: '{{ $user->is_active ? 'Akun tidak akan bisa dipakai masuk sampai diaktifkan kembali.' : 'Akun akan dapat kembali masuk ke LensMart.' }}', confirm: '{{ $user->is_active ? 'Ya, nonaktifkan' : 'Ya, aktifkan' }}', tone: '{{ $user->is_active ? 'warning' : 'primary' }}' }">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-outline btn-sm {{ $user->is_active ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }}">
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $users->links() }}</div>
    @endif
</x-layouts.portal>
