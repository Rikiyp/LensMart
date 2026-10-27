<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('q');

        $orders = Order::query()
            ->with(['store', 'customer', 'payment', 'items'])
            ->when(
                in_array($status, array_column(OrderStatus::cases(), 'value'), true),
                fn ($query) => $query->where('status', $status)
            )
            ->when($search, fn ($query, $keyword) => $query->where('order_number', 'like', "%{$keyword}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'search' => $search,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['store.user', 'customer', 'payment', 'items.product.primaryImage', 'histories.changer', 'payout']);

        return view('admin.orders.show', compact('order'));
    }
}
