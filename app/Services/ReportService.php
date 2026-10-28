<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function completedOrdersQuery(Carbon $from, Carbon $to, ?Store $store = null): Builder
    {
        return Order::query()
            ->with(['store', 'customer', 'payment'])
            ->where('status', OrderStatus::Completed->value)
            ->whereBetween('completed_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($store, fn (Builder $query) => $query->where('store_id', $store->id))
            ->latest('completed_at');
    }

    /**
     * @return array{orders: Collection, count: int, gross: int, subtotal: int, commission: int, shipping: int}
     */
    public function summary(Carbon $from, Carbon $to, ?Store $store = null): array
    {
        $orders = $this->completedOrdersQuery($from, $to, $store)->get();

        return [
            'orders' => $orders,
            'count' => $orders->count(),
            'gross' => (int) $orders->sum('total'),
            'subtotal' => (int) $orders->sum('subtotal'),
            'commission' => (int) $orders->sum('commission_amount'),
            'shipping' => (int) $orders->sum('shipping_cost'),
        ];
    }

    /**
     * Pendapatan platform per bulan untuk grafik dashboard.
     *
     * @return array{labels: array<int, string>, revenue: array<int, int>, commission: array<int, int>}
     */
    public function monthlyRevenue(int $months = 6, ?Store $store = null): array
    {
        $labels = [];
        $revenue = [];
        $commission = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();

            $query = Order::query()
                ->where('status', OrderStatus::Completed->value)
                ->whereBetween('completed_at', [$start, $end])
                ->when($store, fn (Builder $q) => $q->where('store_id', $store->id));

            $labels[] = $start->translatedFormat('M Y');
            $revenue[] = (int) $query->sum('subtotal');
            $commission[] = (int) $query->sum('commission_amount');
        }

        return compact('labels', 'revenue', 'commission');
    }

    /**
     * Top 10 produk terlaris berdasarkan jumlah terjual.
     *
     * @return array{labels: array<int, string>, sales: array<int, int>, revenue: array<int, int>}
     */
    public function topProducts(int $limit = 10, ?Store $store = null, int $days = 30): array
    {
        $from = now()->subDays($days)->startOfDay();

        $items = \App\Models\OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', OrderStatus::Completed->value)
            ->where('orders.completed_at', '>=', $from)
            ->when($store, fn (Builder $q) => $q->where('products.store_id', $store->id))
            ->selectRaw('products.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();

        return [
            'labels' => $items->pluck('name')->toArray(),
            'sales' => $items->pluck('total_quantity')->map(fn ($v) => (int) $v)->toArray(),
            'revenue' => $items->pluck('total_revenue')->map(fn ($v) => (int) $v)->toArray(),
        ];
    }

    /**
     * Distribusi status pesanan.
     *
     * @return array{labels: array<int, string>, counts: array<int, int>}
     */
    public function orderStatusDistribution(?Store $store = null, int $days = 30): array
    {
        $from = now()->subDays($days)->startOfDay();

        $orders = Order::query()
            ->where('created_at', '>=', $from)
            ->when($store, fn (Builder $q) => $q->where('store_id', $store->id))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $statusMap = [
            OrderStatus::WaitingConfirmation->value => 'Menunggu Konfirmasi',
            OrderStatus::Processing->value => 'Diproses',
            OrderStatus::Shipped->value => 'Dikirim',
            OrderStatus::Completed->value => 'Selesai',
            OrderStatus::Cancelled->value => 'Dibatalkan',
        ];

        $labels = [];
        $counts = [];

        foreach ($orders as $order) {
            $labels[] = $statusMap[$order->status] ?? $order->status;
            $counts[] = (int) $order->count;
        }

        return compact('labels', 'counts');
    }

    /**
     * Penjualan per kategori produk.
     *
     * @return array{labels: array<int, string>, revenue: array<int, int>}
     */
    public function categorySales(int $days = 30): array
    {
        $from = now()->subDays($days)->startOfDay();

        $sales = \App\Models\OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', OrderStatus::Completed->value)
            ->where('orders.completed_at', '>=', $from)
            ->selectRaw('categories.name, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->limit(8)
            ->get();

        return [
            'labels' => $sales->pluck('name')->toArray(),
            'revenue' => $sales->pluck('total_revenue')->map(fn ($v) => (int) $v)->toArray(),
        ];
    }

    /**
     * Peak sales hours (jam sibuk penjualan).
     *
     * @return array{labels: array<int, string>, orders: array<int, int>}
     */
    public function peakHours(int $days = 7): array
    {
        $from = now()->subDays($days)->startOfDay();

        $hourly = Order::query()
            ->whereHas('payment', fn ($q) => $q->where('status', 'paid'))
            ->where('created_at', '>=', $from)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $labels = [];
        $orders = [];

        for ($h = 0; $h < 24; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $orders[] = (int) ($hourly->get($h)?->count ?? 0);
        }

        return compact('labels', 'orders');
    }
}
