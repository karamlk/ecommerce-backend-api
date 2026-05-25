<?php

namespace App\Aspects;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DistributedLockAspect
{
    public function acquire(
        string   $key,
        callable $callback,
        int      $lockSeconds = 10,
        int      $waitSeconds = 5
    ): mixed {
        Log::channel('activity')->info('[LOCK] Waiting for distributed lock', [
            'lock_key' => $key,
        ]);

        try {
            return Cache::lock($key, $lockSeconds)
                ->block($waitSeconds, function () use ($key, $callback) {

                    Log::channel('activity')->info('[LOCK] Acquired', [
                        'lock_key' => $key,
                    ]);

                    $failed = false;

                    try {
                        return $callback();
                    } catch (\Throwable $e) {
                        $failed = true;
                        throw $e;
                    } finally {
                        Log::channel('activity')->info('[LOCK] Released', [
                            'lock_key' => $key,
                            'status'   => $failed ? 'released after failure' : 'released cleanly',
                        ]);
                    }
                });
        } catch (LockTimeoutException $e) {
            Log::channel('activity')->warning('[LOCK] Timeout — could not acquire', [
                'lock_key'    => $key,
                'wait_seconds' => $waitSeconds,
            ]);
            throw $e;
        }
    }
}
