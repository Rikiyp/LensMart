<x-layouts.portal title="Pencairan Dana" subtitle="Kelola pembayaran dana partner setelah pesanan selesai.">
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="card p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-ink-500">Menunggu pencairan</p>
                    <p class="mt-2 text-2xl font-extrabold text-amber-600">{{ format_rupiah($totals['pending']) }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><x-icon name="clock" class="h-5 w-5" /></span>
            </div>
            <p class="mt-1 text-[10px] text-ink-400">Dana yang belum ditransfer ke partner</p>
        </div>
        <div class="card p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-ink-500">Sudah dicairkan</p>
                    <p class="mt-2 text-2xl font-extrabold text-emerald-600">{{ format_rupiah($totals['paid']) }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><x-icon name="check-circle" class="h-5 w-5" /></span>
            </div>
            <p class="mt-1 text-[10px] text-ink-400">Total pencairan yang sudah dibayar</p>
        </div>
    </div>

    <div class="mb-5 mt-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.payouts.index') }}"
           class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ ! $status ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
            Semua
        </a>
        @foreach ($statuses as $item)
            <a href="{{ route('admin.payouts.index', ['status' => $item->value]) }}"
               class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ $status === $item->value ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
                {{ $item->label() }}
            </a>
        @endforeach
    </div>

    @if ($payouts->isEmpty())
        <x-empty-state icon="wallet" title="Belum ada pencairan" description="Pencairan dibuat otomatis ketika pesanan selesai." />
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Toko</th>
                            <th>Pesanan</th>
                            <th>Rekening</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payouts as $payout)
                            <tr>
                                <td class="whitespace-nowrap text-xs text-ink-500">{{ format_tanggal($payout->created_at, true) }}</td>
                                <td class="min-w-[150px]">
                                    <a href="{{ $payout->store ? route('admin.partners.show', $payout->store) : '#' }}" class="text-sm font-bold text-ink-950 hover:text-amber-600">{{ $payout->store?->name }}</a>
                                    <p class="text-[10px] text-ink-400">{{ $payout->store?->city }}</p>
                                </td>
                                <td class="text-xs">
                                    @if ($payout->order)
                                        <a href="{{ route('admin.orders.show', $payout->order) }}" class="font-semibold text-ink-700 hover:text-amber-600">{{ $payout->order->order_number }}</a>
                                    @else
                                        <span class="text-ink-400">-</span>
                                    @endif
                                </td>
                                <td class="min-w-[160px] text-[11px] text-ink-500">
                                    <p class="font-semibold text-ink-700">{{ $payout->store?->bank_name ?? '-' }}</p>
                                    <p>{{ $payout->store?->account_number ?? '-' }}</p>
                                    <p>{{ $payout->store?->account_holder ?? '-' }}</p>
                                </td>
                                <td class="whitespace-nowrap text-sm font-extrabold text-ink-950">{{ format_rupiah($payout->amount) }}</td>
                                <td>
                                    <span class="badge {{ $payout->status->badge() }}">{{ $payout->status->label() }}</span>
                                    @if ($payout->paid_at)
                                        <p class="mt-1 text-[10px] text-ink-400">{{ format_tanggal($payout->paid_at, true) }}</p>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($payout->status === \App\Enums\PayoutStatus::Pending)
                                        <form method="POST" action="{{ route('admin.payouts.paid', $payout) }}" x-confirm="{ title: 'Tandai pencairan sudah dibayar?', message: 'Status pencairan akan diubah menjadi selesai. Pastikan dana sudah ditransfer ke toko.', confirm: 'Ya, tandai dibayar', tone: 'warning' }">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-primary btn-sm">Tandai dibayar</button>
                                        </form>
                                    @else
                                        <span class="text-[11px] font-semibold text-emerald-600">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $payouts->links() }}</div>
    @endif
</x-layouts.portal>
