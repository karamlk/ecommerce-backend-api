<?php

namespace App\Aspects;

use Illuminate\Support\Facades\Log;

class TracingAspect
{
    private ?string $traceId = null;

    public function startTrace(): string
    {
        $this->traceId = uniqid('trace_');

        Log::channel('tracing')->info('[TRACE] Request started', [
            'trace_id'  => $this->traceId,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return $this->traceId;
    }

    public function getCurrentTraceId(): string
    {
        if (!$this->traceId) {
            $this->startTrace();
        }

        return $this->traceId;
    }

    public function endTrace(int $statusCode): void
    {
        Log::channel('tracing')->info('[TRACE] Request complete', [
            'trace_id'    => $this->traceId,
            'status_code' => $statusCode,
            'timestamp'   => now()->toDateTimeString(),
        ]);
    }
}