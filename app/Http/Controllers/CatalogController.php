<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ProductCondition;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'category', 'brand', 'condition', 'min_price', 'max_price', 'rating', 'sort']);

        $query = Product::query()
            ->publiclyVisible()
            ->with(['primaryImage', 'brand', 'store', 'category'])
            ->withRating()
            ->withCount(['reviews as reviews_count' => fn (Builder $reviewQuery) => $reviewQuery->where('is_hidden', false)]);

        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function (Builder $searchQuery) use ($keyword) {
                $searchQuery->where('name', 'like', "%{$keyword}%")
                    ->orWhere('model_name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('brand', fn (Builder $brandQuery) => $brandQuery->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('store', fn (Builder $storeQuery) => $storeQuery->where('name', 'like', "%{$keyword}%"));
            });
        }

        if (! empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (! empty($filters['brand'])) {
            $query->where('brand_id', $filters['brand']);
        }

        if (! empty($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $query->where('price', '>=', (int) $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $query->where('price', '<=', (int) $filters['max_price']);
        }

        if (! empty($filters['rating'])) {
            $query->whereRaw(
                '(select avg(rating) from reviews where reviews.product_id = products.id and reviews.is_hidden = 0) >= (? + 0)',
                [(float) $filters['rating']]
            );
        }

        match ($filters['sort'] ?? 'terbaru') {
            'termurah' => $query->orderBy('price'),
            'termahal' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating_avg')->orderByDesc('id'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $compareIds = collect(session('compare', []));

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'conditions' => ProductCondition::cases(),
            'filters' => $filters,
            'compareIds' => $compareIds,
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->isPubliclyVisible(), 404);

        $product->load([
            'images',
            'brand',
            'category',
            'store.user',
            'specs.specAttribute',
            'compatibilities.brand',
            'reviews' => fn ($query) => $query->where('is_hidden', false)->with(['customer', 'images'])->latest(),
        ])->loadCount(['reviews as reviews_count' => fn ($query) => $query->where('is_hidden', false)]);

        $ratingAverage = round((float) $product->reviews->avg('rating'), 1);

        $otherOffers = Product::query()
            ->publiclyVisible()
            ->where('brand_id', $product->brand_id)
            ->where('store_id', '!=', $product->store_id)
            ->whereRaw('lower(model_name) = ?', [mb_strtolower($product->model_name)])
            ->with(['primaryImage', 'store'])
            ->get();

        // Ambil produk terkait terbaru, shuffle di PHP agar ada variasi tanpa
        // membebani DB dengan ORDER BY RAND() yang mahal pada tabel besar.
        $relatedProducts = Product::query()
            ->publiclyVisible()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['primaryImage', 'brand', 'store'])
            ->withRating()
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_hidden', false)])
            ->latest()
            ->limit(12)
            ->get()
            ->shuffle()
            ->take(4);

        $inWishlist = false;
        $reviewableOrder = null;

        if ($request->user()?->isCustomer()) {
            $inWishlist = \App\Models\Wishlist::where('user_id', $request->user()->id)
                ->where('product_id', $product->id)
                ->exists();

            $reviewableOrder = Order::query()
                ->where('customer_id', $request->user()->id)
                ->where('status', OrderStatus::Completed->value)
                ->whereHas('items', fn (Builder $itemQuery) => $itemQuery->where('product_id', $product->id))
                ->whereDoesntHave('reviews', fn (Builder $reviewQuery) => $reviewQuery->where('product_id', $product->id))
                ->latest('completed_at')
                ->first();
        }

        return view('products.show', [
            'product' => $product,
            'ratingAverage' => $ratingAverage,
            'otherOffers' => $otherOffers,
            'relatedProducts' => $relatedProducts,
            'inWishlist' => $inWishlist,
            'inCompare' => in_array($product->id, session('compare', []), true),
            'reviewableOrder' => $reviewableOrder,
        ]);
    }
}
