<?php

use App\Jobs\SendOrderConfirmationJob;
use App\Jobs\SendOtpJob;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

Route::prefix('dev')->group(function () {

    // TK 2: Resource Management & Capacity Control - simulate parallel requests
    Route::get('/simulate-without-lock', function () {

        Http::pool(fn($pool) => [
            $pool->get(url('/dev/demo-race-without/1')),
            $pool->get(url('/dev/demo-race-without/2')),
        ]);

        return response()->json([
            'message' => 'Concurrent requests executed (without lock)'
        ]);
    });


    // TK 2: Resource Management & Capacity Control - simulate protected concurrency
    Route::get('/simulate-with-lock', function () {

        Http::pool(fn($pool) => [
            $pool->get(url('/dev/demo-race/1')),
            $pool->get(url('/dev/demo-race/2')),
        ]);

        return response()->json([
            'message' => 'Concurrent requests executed (with lock)'
        ]);
    });


    // TK 2: Resource Management & Capacity Control - OTP load simulation
    Route::get('/simulate-otp-load', function () {

        $emails = [
            'a@test.com',
            'b@test.com',
            'c@test.com',
            'd@test.com',
            'e@test.com',
        ];

        foreach ($emails as $email) {
            dispatch(new SendOtpJob(
                $email,
                rand(100000, 999999)
            ));
        }

        return response()->json([
            'message' => 'OTP load simulation dispatched'
        ]);
    });


    // TK 2: Resource Management & Capacity Control -- single OTP test
    Route::get('/simulate-otp-single', function () {

        dispatch(new SendOtpJob(
            'test@example.com',
            rand(100000, 999999)
        ));

        return response()->json([
            'message' => 'Single OTP job dispatched'
        ]);
    });

    // TK 1: Concurrent Access & Data Integrity - Race condition demo (WITH LOCK)
    Route::get('/demo-race/{id}', function ($id) {
        return app(OrderService::class)
            ->createOrderFromCart($id);
    });

    // TK 1: Concurrent Access & Data Integrity - Race condition demo (WITHOUT LOCK)
    Route::get('/demo-race-without/{id}', function ($id) {
        return app(OrderService::class)
            ->createOrderFromCartWithoutLock($id);
    });

    // TK 3: BEFORE — sync
    Route::post('/simulate-order-sync', function (OrderService $orderService) {

        $start = microtime(true);

        $orderService->createOrderFromCart(auth('sanctum')->id());
        $order = Order::latest()->first();

        // Synchronous — blocks until email is sent
        Mail::to($order->user->email)
            ->send(
                new OrderConfirmationMail($order->id, $order->total, $order->status)
            );

        $duration = round((microtime(true) - $start) * 1000, 2);

        return response()->json([
            'mode'        => 'BEFORE — synchronous',
            'duration_ms' => $duration,
        ]);
    });

    // TK 3: AFTER — async
    Route::post('/simulate-order-async', function (\App\Services\Order\OrderService $orderService) {
        
        $start = microtime(true);

        $orderService->createOrderFromCart(auth('sanctum')->id());
        $order = Order::latest()->first();

        // Async — just pushes to Redis, returns immediately
        SendOrderConfirmationJob::dispatch(
            $order->id,
            $order->user_id,
            (float) $order->total,
            $order->status,
            $order->user->email,
        );

        $duration = round((microtime(true) - $start) * 1000, 2);

        return response()->json([
            'mode'        => 'AFTER — async queue',
            'duration_ms' => $duration,
        ]);
    });
});
