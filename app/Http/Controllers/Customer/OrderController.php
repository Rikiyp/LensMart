<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly OrderStatusService $orderStatusService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->paymentService->expireDueFor($request->user());

        $status = $request->query('status');
        $validStatuses = array_column(OrderStatus::cases(), 'value');

        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->when($status && in_array($status, $validStatuses, true), fn ($query) => $query->where('status', $status))
            ->with(['store', 'payment', 'items'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customer.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['store', 'payment', 'items.product.primaryImage', 'histories.changer', 'reviews']);

        return view('customer.orders.show', [
            'order' => $order,
            'canReview' => $order->status === OrderStatus::Completed,
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->orderStatusService->cancel($order, $request->user(), $validated['note'] ?? null);

        return back()->with('success', 'Pesanan berhasil dibatalkan dan stok dikembalikan.');
    }

    public function confirm(Order $order, OrderStatusService $orderStatusService): RedirectResponse
    {
        $this->authorize('confirmReceipt', $order);

        $orderStatusService->confirmReceived($order, request()->user());

        return back()->with('success', 'Terima kasih! Pesanan ditandai selesai.');
    }
}
