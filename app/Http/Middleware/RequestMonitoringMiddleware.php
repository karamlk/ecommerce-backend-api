<?php

namespace App\Http\Middleware;

use App\Aspects\TracingAspect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestMonitoringMiddleware
{
    public function __construct(private TracingAspect $tracing) {}

    public function handle(Request $request, Closure $next): Response
    {
        $start   = microtime(true);
        $traceId = $this->tracing->startTrace();

        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::channel('tracing')->info('[TRACE] Request complete', [
            'trace_id'    => $traceId,
            'method'      => $request->method(),
            'url'         => $request->path(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
        ]);

        $response->headers->set('X-Trace-Id', $traceId);
        $response->headers->set('X-Duration-Ms', $duration);

        return $response;
    }
}
