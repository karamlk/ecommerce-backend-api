<?php

namespace App\Aspects;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ErrorHandlingAspect
{
    public function handle(string $label, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (ModelNotFoundException $e) {
            Log::channel('activity')->warning("[NOT FOUND] {$label}", [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } catch (ValidationException $e) {
            Log::channel('activity')->warning("[VALIDATION] {$label}", [
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::channel('activity')->error("[EXCEPTION] {$label}", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace_id' => app(TracingAspect::class)->getCurrentTraceId(),
            ]);
            throw $e;
        }
    }
}
