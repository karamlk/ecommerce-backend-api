<?php

namespace App\Aspects;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

// Task 6: Distributed caching
class CacheAspect
{
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = Cache::get($key);

        if ($cached !== null) {
            $this->logStatus($key, 'HIT');
            return $cached;
        }

        $this->logStatus($key, 'MISS');

        $value = $callback();
        Cache::put($key, $value, $ttl);
        return $value;
    }

    public function increment(string $key): void
    {
        Cache::increment($key);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
        $this->logStatus($key, 'INVALIDATED');
    }

    private function logStatus(string $key, string $status): void
    {
        Log::channel('performance')->info("[CACHE_STATUS] {$status}", [
            'key'       => $key,
            'status'    => $status,
            'timestamp' => now()->toDateTimeString(),
            'trace_id'  => app(TracingAspect::class)->getCurrentTraceId(),
        ]);
    }
}
