<?php

namespace App\Http\Middleware;

use App\Aspects\TracingAspect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestMonitoringMiddleware
{
     public function __construct(private TracingAspect $tracing) {}

    public function handle(Request $request, Closure $next): Response
    {
        $traceId = $this->tracing->startTrace();

        $response = $next($request);

        $response->headers->set('X-Trace-Id', $traceId);

        $this->tracing->endTrace($response->getStatusCode());

        return $response;
    }
}
