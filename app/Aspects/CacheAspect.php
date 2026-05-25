<?php

namespace App\Aspects;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

// Task 6: Distributed caching
class CacheAspect
{
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        if (Cache::has($key)) {
            return Cache::get($key);
        }

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

        Log::channel('performance')->info('[CACHE INVALIDATED]', [
            'key' => $key,
        ]);
    }
}
