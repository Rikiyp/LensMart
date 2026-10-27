<x-layouts.portal title="Semua Pesanan" subtitle="Pantau transaksi lintas toko dan status pembayaran.">
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.orders.index', ['q' => $search]) }}"
           class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ ! $status ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
            Semua
        </a>
        @foreach ($statuses as $item)
            <a href="{{ route('admin.orders.index', ['status' => $item->value, 'q' => $search]) }}"
               class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ $status === $item->value ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
                {{ $item->label() }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="card mb-5 flex flex-col gap-2 p-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nomor pesanan…" class="input !py-2 pl-10 text-xs">
        </div>
        <select name="status" class="select !py-2 text-xs sm:w-52">
            <option value="">Semua status</option>
            @foreach ($statuses as $item)
                <option value="{{ $item->value }}" @selected($status === $item->value)>{{ $item->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary btn-sm">Filter</button>
        @if ($search || $status)
            <a href="{{ route('admin.orders.index') }}" class="btn-ghost btn-sm justify-center">Reset</a>
        @endif
    </form>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <p class="text-xs text-ink-500">{{ $orders->total() }} pesanan ditemukan</p>
        <span class="badge bg-paper-100 text-ink-600 ring-ink-500/15">{{ $orders->count() }} di halaman ini</span>
    </div>

    @if ($orders->isEmpty())
        <x-empty-state icon="shopping-bag" title="Tidak ada pesanan" description="Belum ada transaksi yang sesuai dengan filter ini." />
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Toko</th>
                            <th>Item</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="whitespace-nowrap">
                                    <p class="text-sm font-bold text-ink-950">{{ $order->order_number }}</p>
                                    <p class="text-[10px] text-ink-400">{{ format_tanggal($order->created_at, true) }}</p>
                                </td>
                                <td class="min-w-[140px]">
                                    <p class="text-xs font-semibold text-ink-900">{{ $order->customer?->name }}</p>
                                    <p class="text-[10px] text-ink-400">{{ $order->customer?->email }}</p>
                                </td>
                                <td class="min-w-[130px] text-xs text-ink-600">{{ $order->store?->name }}</td>
                                <td class="whitespace-nowrap text-xs text-ink-600">{{ $order->items->sum('quantity') }} item</td>
                                <td class="whitespace-nowrap text-sm font-extrabold text-ink-950">{{ format_rupiah($order->total) }}</td>
                                <td>
                                    <span class="badge {{ $order->payment?->status->badge() ?? 'bg-paper-100 text-ink-600 ring-ink-500/15' }}">{{ $order->payment?->status->label() ?? '-' }}</span>
                                    @if ($order->payment?->method_label)
                                        <p class="mt-1 text-[10px] text-ink-400">{{ $order->payment->method_label }}</p>
                                    @endif
                                </td>
                                <td><span class="badge {{ $order->status->badge() }}">{{ $order->status->label() }}</span></td>
                                <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="btn-outline btn-sm">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $orders->links() }}</div>
    @endif
</x-layouts.portal>
