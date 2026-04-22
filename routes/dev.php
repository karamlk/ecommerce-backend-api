<?php

use App\Jobs\SendOtpJob;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

// TK 1: Concurrent Access & Data Integrity - Race condition demo (WITH LOCK)
Route::get('/demo-race/{id}', function ($id) {
    return app(OrderService::class)
        ->createOrderFromCart($id);
});

// TK 1: Concurrent Access & Data Integrity - Race condition demo (WITHOUT LOCK)
// Route::get('/demo-race-without/{id}', function ($id) {
//     return app(OrderService::class)
//         ->createOrderFromCartWithoutLock($id);
// });


// TK 2: Resource Management & Capacity Control - simulate parallel requests
Route::get('/simulate', function () {

    Http::pool(fn ($pool) => [
        $pool->get(url('/demo-race-without/1')),
        $pool->get(url('/demo-race-without/2')),
    ]);

    return response()->json([
        'message' => 'Concurrent requests executed (without lock)'
    ]);
});


// TK 2: Resource Management & Capacity Control - simulate protected concurrency
Route::get('/simulate-with-lock', function () {

    Http::pool(fn ($pool) => [
        $pool->get(url('/demo-race/1')),
        $pool->get(url('/demo-race/2')),
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


// TK 2: Resource Management & Capacity Control - single OTP test
Route::get('/simulate-otp-single', function () {

    dispatch(new SendOtpJob(
        'test@example.com',
        rand(100000, 999999)
    ));

    return response()->json([
        'message' => 'Single OTP job dispatched'
    ]);
});