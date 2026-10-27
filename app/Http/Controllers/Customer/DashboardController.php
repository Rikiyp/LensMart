<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PaymentService $paymentService): View
    {
        $user = $request->user();
        $paymentService->expireDueFor($user);

        $orders = Order::query()
            ->where('customer_id', $user->id)
            ->with(['store', 'payment', 'items'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'orders' => Order::where('customer_id', $user->id)->count(),
            'unpaid' => Order::where('customer_id', $user->id)
                ->whereHas('payment', fn ($query) => $query->where('status', PaymentStatus::Unpaid->value))
                ->count(),
            'completed' => Order::where('customer_id', $user->id)->where('status', OrderStatus::Completed->value)->count(),
            'wishlist' => Wishlist::where('user_id', $user->id)->count(),
        ];

        $featuredProducts = Product::query()
            ->publiclyVisible()
            ->with(['primaryImage', 'brand', 'store'])
            ->withRating()
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_hidden', false)])
            ->latest()
            ->limit(4)
            ->get();

        return view('customer.dashboard', compact('orders', 'stats', 'featuredProducts'));
    }
}
