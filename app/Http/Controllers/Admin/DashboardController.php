<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StoreStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReviewReport;
use App\Models\Store;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reportService): View
    {
        $stats = [
            'customers' => User::where('role', 'customer')->count(),
            'partners' => Store::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'revenue' => (int) Order::where('status', OrderStatus::Completed->value)->sum('total'),
            'commission' => (int) Order::where('status', OrderStatus::Completed->value)->sum('commission_amount'),
            'pending_stores' => Store::where('status', StoreStatus::Pending->value)->count(),
            'pending_products' => Product::where('verification_status', VerificationStatus::Pending->value)->count(),
            'open_reports' => ReviewReport::where('status', 'open')->count(),
            'unpaid_payments' => \App\Models\Payment::where('status', PaymentStatus::Unpaid->value)->count(),
        ];

        $recentOrders = Order::with(['store', 'customer', 'payment'])
            ->latest()
            ->limit(8)
            ->get();

        $topStores = Store::query()
            ->withCount(['orders as completed_orders_count' => fn ($query) => $query->where('status', OrderStatus::Completed->value)])
            ->withSum(['orders as completed_sales' => fn ($query) => $query->where('status', OrderStatus::Completed->value)], 'subtotal')
            ->orderByDesc('completed_sales')
            ->limit(5)
            ->get();

        $chart = $reportService->monthlyRevenue(6);

        return view('admin.dashboard', compact('stats', 'recentOrders', 'topStores', 'chart'));
    }
}
