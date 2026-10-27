<x-layouts.portal title="Pesanan Masuk" subtitle="Proses pesanan yang sudah dibayar pelanggan.">
    @php
        $tabs = [
            ['value' => null, 'label' => 'Semua'],
            ...collect($statuses)->map(fn ($item) => ['value' => $item->value, 'label' => $item->label()])->all(),
        ];
    @endphp

    <div class="mb-5 flex flex-wrap gap-2">
        @foreach ($tabs as $tab)
            <a href="{{ route('partner.orders.index', array_filter(['status' => $tab['value']])) }}"
               class="rounded-full px-3.5 py-2 text-xs font-bold transition {{ (string) $status === (string) $tab['value'] ? 'bg-ink-950 text-white' : 'border border-paper-300 bg-white text-ink-600 hover:border-paper-400 hover:text-ink-950' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    @if ($orders->isEmpty())
        <x-empty-state icon="shopping-bag" title="Belum ada pesanan" description="Pesanan yang sudah dibayar pelanggan akan muncul di sini untuk Anda proses.">
            <a href="{{ route('partner.products.index') }}" class="btn-primary btn-sm">Kelola produk</a>
        </x-empty-state>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Item</th>
                            <th>Total</th>
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
                                <td class="min-w-[150px]">
                                    <p class="text-sm font-semibold text-ink-900">{{ $order->customer->name }}</p>
                                    <p class="text-[10px] text-ink-400">{{ $order->customer->email }}</p>
                                </td>
                                <td class="min-w-[180px]">
                                    <p class="text-xs text-ink-600">{{ $order->items->sum('quantity') }} item</p>
                                    <p class="line-clamp-1 text-[10px] text-ink-400">{{ $order->items->pluck('product_name')->join(', ') }}</p>
                                </td>
                                <td class="whitespace-nowrap text-sm font-bold text-ink-950">{{ format_rupiah($order->total) }}</td>
                                <td>
                                    <span class="badge {{ $order->status->badge() }}">{{ $order->status->label() }}</span>
                                    @if ($order->tracking_number)
                                        <p class="mt-1 font-mono text-[10px] text-ink-400">{{ $order->tracking_number }}</p>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('partner.orders.show', $order) }}" class="btn-outline btn-sm">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $orders->links() }}</div>
    @endif
</x-layouts.portal>
