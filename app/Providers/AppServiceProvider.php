<?php

namespace App\Providers;

use App\Aspects\ExecutionAspect;
use App\Aspects\TracingAspect;
use App\Aspects\TransactionAspect;
use App\Decorators\CartServiceDecorator;
use App\Decorators\FavoriteServiceDecorator;
use App\Decorators\OrderServiceDecorator;
use App\Decorators\ProfileServiceDecorator;
use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Services\Cart\CartService;
use App\Services\Favorite\FavoriteService;
use App\Services\Order\OrderService;
use App\Services\Profile\ProfileService;
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

        $this->app->bind(OrderService::class, function ($app) {
            return new OrderServiceDecorator(
                new OrderService(),
                $app->make(ExecutionAspect::class),
                $app->make(TransactionAspect::class),
            );
        });

        $this->app->bind(CartService::class, function ($app) {
            return new CartServiceDecorator(
                new CartService(),
                $app->make(ExecutionAspect::class),
                $app->make(TransactionAspect::class),
            );
        });

        $this->app->bind(FavoriteService::class, function ($app) {
            return new FavoriteServiceDecorator(
                new FavoriteService(),
                $app->make(ExecutionAspect::class),
                $app->make(TransactionAspect::class),
            );
        });

        $this->app->bind(ProfileService::class, function ($app) {
            return new ProfileServiceDecorator(
                new ProfileService(),
                $app->make(ExecutionAspect::class),
                $app->make(TransactionAspect::class),
            );
        });
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
    }
}
