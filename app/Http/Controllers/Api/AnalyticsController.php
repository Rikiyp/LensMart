<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    public function dashboard(Request $request)
    {
        $store = $request->user()->store;
        $cacheKey = 'analytics.dashboard.' . ($store?->id ?? 'global');

        return Cache::remember($cacheKey, 3600, function () use ($store) {
            return [
                'monthlyRevenue' => $this->reportService->monthlyRevenue(6, $store),
                'topProducts' => $this->reportService->topProducts(10, $store, 30),
                'orderStatusDistribution' => $this->reportService->orderStatusDistribution($store, 30),
                'categorySales' => $store ? [] : $this->reportService->categorySales(30),
                'peakHours' => $this->reportService->peakHours(7),
            ];
        });
    }

    public function revenue(Request $request)
    {
        $request->validate([
            'months' => 'nullable|integer|min:1|max:24',
            'store' => 'nullable|exists:stores,id',
        ]);

        $store = $request->filled('store')
            ? Store::find($request->store)
            : $request->user()->store;

        $months = $request->months ?? 6;

        $cacheKey = sprintf(
            'analytics.revenue.%s.%d',
            $store?->id ?? 'global',
            $months
        );

        return Cache::remember($cacheKey, 3600, function () use ($months, $store) {
            return $this->reportService->monthlyRevenue($months, $store);
        });
    }

    public function topProducts(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:20',
            'days' => 'nullable|integer|min:1|max:365',
            'store' => 'nullable|exists:stores,id',
        ]);

        $store = $request->filled('store')
            ? Store::find($request->store)
            : $request->user()->store;

        $limit = $request->limit ?? 10;
        $days = $request->days ?? 30;

        $cacheKey = sprintf(
            'analytics.top_products.%s.%d.%d',
            $store?->id ?? 'global',
            $limit,
            $days
        );

        return Cache::remember($cacheKey, 1800, function () use ($limit, $store, $days) {
            return $this->reportService->topProducts($limit, $store, $days);
        });
    }

    public function orderStatus(Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
            'store' => 'nullable|exists:stores,id',
        ]);

        $store = $request->filled('store')
            ? Store::find($request->store)
            : $request->user()->store;

        $days = $request->days ?? 30;

        $cacheKey = sprintf(
            'analytics.order_status.%s.%d',
            $store?->id ?? 'global',
            $days
        );

        return Cache::remember($cacheKey, 1800, function () use ($store, $days) {
            return $this->reportService->orderStatusDistribution($store, $days);
        });
    }

    public function categories(Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        $days = $request->days ?? 30;

        $cacheKey = sprintf(
            'analytics.category_sales.global.%d',
            $days
        );

        return Cache::remember($cacheKey, 3600, function () use ($days) {
            return $this->reportService->categorySales($days);
        });
    }

    public function hours(Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:30',
        ]);

        $days = $request->days ?? 7;

        $cacheKey = sprintf(
            'analytics.peak_hours.%d',
            $days
        );

        return Cache::remember($cacheKey, 1800, function () use ($days) {
            return $this->reportService->peakHours($days);
        });
    }
}