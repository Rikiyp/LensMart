<?php

namespace App\Http\Controllers\Partner;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;
        $status = $request->query('status');
        $validStatuses = array_column(OrderStatus::cases(), 'value');

        $orders = Order::query()
            ->where('store_id', $store->id)
            ->whereHas('payment', fn ($query) => $query->where('status', 'paid'))
            ->when($status && in_array($status, $validStatuses, true), fn ($query) => $query->where('status', $status))
            ->with(['items', 'customer', 'payment'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('partner.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items.product.primaryImage', 'customer', 'payment', 'histories.changer', 'store']);

        return view('partner.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, OrderStatusService $orderStatusService): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        if ($request->input('action') === 'cancel') {
            $orderStatusService->cancel($order, $request->user(), $request->input('note') ?: 'Pesanan ditolak oleh toko.');

            return back()->with('success', 'Pesanan dibatalkan dan stok dikembalikan.');
        }

        $orderStatusService->advance(
            $order,
            $request->user(),
            $request->input('tracking_number'),
            $request->input('note')
        );

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
