<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Task 4
Schedule::command('sales:process-daily')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::channel('activity')
            ->info('[SCHEDULER] Daily sales command dispatched successfully');
    })
    ->onFailure(function () {
        Log::channel('activity')
            ->error('[SCHEDULER] Daily sales command failed');
    });
