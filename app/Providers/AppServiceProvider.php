<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Wishlist;
use App\Observers\ProductObserver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Product::observe(ProductObserver::class);

        View::composer('components.product-card', function ($view) {
            $request = app(Request::class);
            $key = 'lensmart.wishlist_product_ids';

            if (! $request->attributes->has($key)) {
                $user = $request->user();

                $request->attributes->set(
                    $key,
                    $user
                        ? Wishlist::query()->where('user_id', $user->id)->pluck('product_id')->all()
                        : []
                );
            }

            $view->with('wishlistedProductIds', $request->attributes->get($key));
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
