<?php

namespace App\Http\Controllers\Partner;

use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ReportService $reportService): View
    {
        $store = $request->user()->store;

        abort_unless($store, 403);

        $paidOrders = Order::query()
            ->where('store_id', $store->id)
            ->whereHas('payment', fn ($query) => $query->where('status', 'paid'));

        $stats = [
            'new_orders' => (clone $paidOrders)->where('status', OrderStatus::WaitingConfirmation->value)->count(),
            'products' => Product::where('store_id', $store->id)->count(),
            'active_products' => Product::where('store_id', $store->id)->where('status', ProductStatus::Active->value)->count(),
            'sales' => (int) Order::where('store_id', $store->id)->where('status', OrderStatus::Completed->value)->sum('subtotal'),
            'commission' => (int) Order::where('store_id', $store->id)->where('status', OrderStatus::Completed->value)->sum('commission_amount'),
            'pending_payout' => (int) Payout::where('store_id', $store->id)->where('status', PayoutStatus::Pending->value)->sum('amount'),
            'reviews' => Review::whereHas('product', fn ($query) => $query->where('store_id', $store->id))->count(),
        ];

        $recentOrders = (clone $paidOrders)
            ->with(['items', 'customer', 'payment'])
            ->latest()
            ->limit(6)
            ->get();

        $lowStockProducts = Product::where('store_id', $store->id)
            ->where('stock', '<=', 3)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        $storeReviewQuery = Review::query()
            ->where('is_hidden', false)
            ->whereHas('product', fn ($query) => $query->where('store_id', $store->id));

        $latestReviews = (clone $storeReviewQuery)
            ->with(['customer', 'product:id,name,slug'])
            ->latest()
            ->limit(2)
            ->get();

        $reviewAverage = round((float) $storeReviewQuery->avg('rating'), 1);

        $chart = $reportService->monthlyRevenue(6, $store);

        return view('partner.dashboard', compact('store', 'stats', 'recentOrders', 'lowStockProducts', 'latestReviews', 'reviewAverage', 'chart'));
    }
}
