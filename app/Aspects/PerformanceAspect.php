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
        $startQueries = count(DB::getQueryLog());

        try {
            return $callback();
        } finally {
            Log::channel('performance')->info("[PERF] {$label}", [
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
                'memory_kb'   => round((memory_get_usage() - $startMemory) / 1024, 1),
                'queries'     => count(DB::getQueryLog()) - $startQueries,
                'timestamp'   => now()->toDateTimeString(),
                'trace_id' => app(TracingAspect::class)->getCurrentTraceId(),
            ]);
            DB::flushQueryLog();
        }
    }
}