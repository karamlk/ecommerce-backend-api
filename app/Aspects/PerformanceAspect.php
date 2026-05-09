<?php

namespace App\Aspects;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceAspect
{
    public function measure(string $label, callable $callback): mixed
    {
        $start        = microtime(true);
        $startMemory  = memory_get_usage();
        $beforeCount  = count(DB::getQueryLog()); // snapshot, never flush

        try {
            return $callback();
        } finally {
            $duration = round((microtime(true) - $start) * 1000, 2);
            $queries  = array_slice(DB::getQueryLog(), $beforeCount);

            Log::channel('performance')->info("[PERF] {$label}", [
                'duration_ms' => $duration,
                'memory_delta_kb' => round((memory_get_usage() - $startMemory) / 1024, 1),
                'queries' => count($queries),
                'timestamp' => now()->toDateTimeString(),
                'trace_id' => app(TracingAspect::class)->getCurrentTraceId(),
            ]);

            if ($duration > 500) {
                Log::channel('performance')->warning("[SLOW] {$label} took {$duration}ms");
            }
        }
    }
}
