<?php

namespace App\Aspects;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceAspect
{
    public function measure(string $label, callable $callback): mixed
    {
        $start        = microtime(true);
        $startMem    = memory_get_usage(true);        // Starting real memory
        $startPeak   = memory_get_peak_usage(true);
        $beforeCount  = count(DB::getQueryLog()); // snapshot, never flush

        try {
            return $callback();
        } finally {
            $duration = round((microtime(true) - $start) * 1000, 2);
            $endMem      = memory_get_usage(true);
            $endPeak     = memory_get_peak_usage(true);
            $queries  = array_slice(DB::getQueryLog(), $beforeCount);
            $cacheStatus = count($queries) === 0 ? 'HIT' : 'MISS';


            Log::channel('performance')->info("[PERF] {$label}", [
                'duration_ms' => $duration,
                'memory_used_kb'    => round(($endMem - $startMem) / 1024, 2),
                'memory_peak_kb'    => round($endPeak / 1024, 2),
                'memory_delta_kb'   => round(($endPeak - $startPeak) / 1024, 2),
                'queries' => count($queries),
                'cache_status' => $cacheStatus,
                'timestamp' => now()->toDateTimeString(),
                'trace_id' => app(TracingAspect::class)->getCurrentTraceId(),
            ]);

            if ($duration > 500) {
                Log::channel('performance')->warning("[SLOW] {$label} took {$duration}ms");
            }
        }
    }
}
