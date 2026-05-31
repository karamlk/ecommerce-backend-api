<?php

namespace App\Services\Payment;

use App\Aspects\ExecutionAspect;

class PaymentService
{
    public function __construct(private ExecutionAspect $execution) {}

    public function processPayment(int $userId, float $amount, bool $shouldFail = true): bool
    {
        return $this->execution->run(
            'PaymentService::processPayment',
            function () use ($shouldFail) {

                if ($shouldFail) {
                    throw new \Exception('Payment failed: Insufficient funds');
                }

                return true;
            }
        );
    }
}
