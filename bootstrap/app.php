<?php

use App\Http\Middleware\RequestMonitoringMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
          then: function () {
            if (app()->environment('local')) {
                require base_path('routes/dev.php');
            }
        }
        // Add this line commands: __DIR__ . '/../routes/console.php', health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(RequestMonitoringMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
