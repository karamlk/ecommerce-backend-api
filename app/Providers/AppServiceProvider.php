<?php

namespace App\Providers;

use App\Aspects\ExecutionAspect;
use App\Aspects\LoggingAspect;
use App\Aspects\PerformanceAspect;
use App\Aspects\TracingAspect;
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
        $this->app->singleton(PerformanceAspect::class);
        $this->app->singleton(LoggingAspect::class);
        $this->app->singleton(ExecutionAspect::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.debug')) {
            DB::enableQueryLog();
        }
    }
}
