<?php

namespace App\Aspects;

use Illuminate\Support\Facades\Log;

class LoggingAspect
{
    public function __construct(private TracingAspect $tracing) {}

    public function wrap(string $label, callable $callback): mixed
    {
        $traceId = $this->tracing->getCurrentTraceId();

        Log::channel('activity')->info("[START] {$label}", [
            'trace_id' => $traceId
        ]);

        try {
            $result = $callback();

            Log::channel('activity')->info("[END] {$label}", [
                'trace_id' => $traceId
            ]);

            return $result;
        } catch (\Throwable $e) {
            Log::channel('activity')->error("[FAILED] {$label}", [
                'trace_id' => $traceId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
