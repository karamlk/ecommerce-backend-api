<?php

namespace App\Decorators;

use App\Aspects\ExecutionAspect;
use App\Aspects\TransactionAspect;

abstract class BaseServiceDecorator
{
    public function __construct(
        protected object            $service,
        protected ExecutionAspect   $execution,
        protected TransactionAspect $transaction,
    ) {}

    protected function run(string $method, array $args = [], bool $transactional = false): mixed
    {
        $label = get_class($this->service) . '::' . $method;

        return $this->execution->run($label, function () use ($method, $args, $transactional) {
            
            if ($transactional) {
                return $this->transaction->run(
                    fn() => $this->service->$method(...$args)
                );
            }

            return $this->service->$method(...$args);
        });
    }
}
