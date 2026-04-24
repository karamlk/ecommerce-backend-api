<?php

namespace App\Aspects;

class ExecutionAspect
{
    public function __construct(
        private PerformanceAspect $performance,
        private LoggingAspect     $logging,
        private TracingAspect     $tracing,
    ) {}

    public function run(string $label, callable $callback): mixed
    {
        return $this->performance->measure($label, function () use ($label, $callback) {
            return $this->logging->wrap($label, function () use ($callback) {
                return $callback();
            });
        });
    }
}