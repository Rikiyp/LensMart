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
}
