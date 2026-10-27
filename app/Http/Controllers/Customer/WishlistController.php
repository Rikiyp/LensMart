<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $items = Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->with(['product.primaryImage', 'product.brand', 'product.store'])
            ->latest()
            ->paginate(12);

        return view('customer.wishlist', compact('items'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->isPubliclyVisible(), 404);

        Wishlist::firstOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            ['price_when_added' => $product->price]
        );

        return back()->with('success', "{$product->name} ditambahkan ke wishlist.");
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return back()->with('success', 'Produk dihapus dari wishlist.');
    }
}
