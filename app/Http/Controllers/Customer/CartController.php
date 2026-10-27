<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index(Request $request): View
    {
        $items = CartItem::query()
            ->where('user_id', $request->user()->id)
            ->with(['product.primaryImage', 'product.store', 'product.brand'])
            ->get()
            ->filter(fn (CartItem $item) => $item->product !== null)
            ->values();

        $groups = $items->groupBy(fn (CartItem $item) => $item->product->store_id);

        $itemData = $items->mapWithKeys(fn (CartItem $item) => [
            $item->id => [
                'quantity' => $item->quantity,
                'subtotal' => $item->product->price * $item->quantity,
            ],
        ]);

        return view('customer.cart', [
            'groups' => $groups,
            'items' => $items,
            'itemData' => $itemData,
            'subtotal' => $items->sum(fn (CartItem $item) => $item->product->price * $item->quantity),
            'itemCount' => $items->sum('quantity'),
        ]);
    }

    public function prepareCheckout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['integer', 'distinct'],
        ], [
            'items.required' => 'Pilih minimal satu produk untuk di-checkout.',
            'items.min' => 'Pilih minimal satu produk untuk di-checkout.',
            'items.*.integer' => 'Pilihan produk tidak valid.',
            'items.*.distinct' => 'Pilihan produk tidak valid.',
        ]);

        $items = CartItem::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $validated['items'])
            ->get();

        if ($items->count() !== count($validated['items'])) {
            return back()->with('error', 'Sebagian produk tidak ditemukan di keranjang. Muat ulang halaman lalu coba lagi.');
        }

        $request->session()->put('checkout.cart_item_ids', $items->pluck('id')->all());

        return redirect()->route('customer.checkout.index');
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $this->cartService->add($request->user(), $product, (int) ($validated['quantity'] ?? 1));

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $this->cartService->update($cartItem, (int) $validated['quantity']);

        return back()->with('success', 'Jumlah produk diperbarui.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 403);

        $this->cartService->remove($cartItem);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
