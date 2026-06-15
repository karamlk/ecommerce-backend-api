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

        $port   = $request->server('SERVER_PORT');
        $server = match ($port) {
            '8001'  => 'server-1',
            '8002'  => 'server-2',
            '8003'  => 'server-3',
            default => 'server-main',
        };

        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::channel('tracing')->info('[TRACE] Request complete', [
            'trace_id'    => $traceId,
            'method'      => $request->method(),
            'url'         => $request->path(),
            'status_code' => $response->getStatusCode(),
            'port' => $port,
            'duration_ms' => $duration,
        ]);

        $response->headers->set('X-Trace-Id', $traceId);
        $response->headers->set('X-Duration-Ms', $duration);
        $response->headers->set('X-Handled-By', $server);

        return $response;
    }
}
