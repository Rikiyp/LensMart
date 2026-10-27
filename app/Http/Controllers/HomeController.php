<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\StoreStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::query()
            ->withCount(['products as public_products_count' => fn ($query) => $query->publiclyVisible()])
            ->orderBy('name')
            ->get();

        $latestProducts = Product::query()
            ->publiclyVisible()
            ->where('status', ProductStatus::Active->value)
            ->with(['primaryImage', 'brand', 'store', 'category'])
            ->withRating()
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_hidden', false)])
            ->latest()
            ->limit(8)
            ->get();

        $topProducts = Product::query()
            ->publiclyVisible()
            ->where('status', ProductStatus::Active->value)
            ->with(['primaryImage', 'brand', 'store', 'category'])
            ->withRating()
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_hidden', false)])
            ->orderByDesc('rating_avg')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $featuredBrandSlugs = ['sony', 'canon', 'nikon', 'fujifilm', 'panasonic', 'dji', 'godox', 'sigma'];

        $featuredBrands = Brand::query()
            ->whereIn('slug', $featuredBrandSlugs)
            ->get()
            ->keyBy('slug');

        return view('home', [
            'categories' => $categories,
            'featuredBrands' => collect($featuredBrandSlugs)
                ->map(fn (string $slug) => $featuredBrands->get($slug))
                ->filter(),
            'latestProducts' => $latestProducts,
            'topProducts' => $topProducts,
            'productCount' => Product::query()->publiclyVisible()->count(),
            'storeCount' => Store::query()->where('status', StoreStatus::Verified->value)->count(),
            'customerCount' => User::query()->where('role', 'customer')->count(),
            'orderCount' => Order::query()->where('status', OrderStatus::Completed->value)->count(),
        ]);
    }
}
