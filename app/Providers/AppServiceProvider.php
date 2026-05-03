<?php

namespace App\Providers;

use App\Aspects\TracingAspect;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Observers\CartItemObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TracingAspect::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.debug')) {
            DB::enableQueryLog();
        }

        Order::observe(OrderObserver::class);
        Product::observe(ProductObserver::class);
        User::observe(UserObserver::class);       
        CartItem::observe(CartItemObserver::class);
    }
}
