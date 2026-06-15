<?php

namespace App\Jobs;

use App\Services\Order\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Throwable;

// Task 10
// ShouldBeUnique
class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 20;

    public int $backoff = 30;

    // public int $uniqueFor = 10;

    public function __construct(
        public int $userId
    ) {
        $this->onQueue('orders');
    }

    // public function uniqueId(): string
    // {
    //     return "process-order-user:{$this->userId}";
    // }

    public function handle(
        OrderService $orderService
    ): void {

        Redis::throttle('process-orders')
            ->allow(100)
            ->every(60)
            ->then(function () use ($orderService) {

                Log::channel('activity')->info(
                    '[ORDER PROCESSING] Started',
                    [
                        'user_id' => $this->userId,
                    ]
                );

                $order = $orderService
                    ->createOrderFromCart(
                        $this->userId
                    );

                if (!$order) {

                    Log::channel('activity')->warning(
                        '[ORDER PROCESSING] Empty cart',
                        [
                            'user_id' => $this->userId,
                        ]
                    );

                    return;
                }

                SendOrderConfirmationJob::dispatch(
                    $order->id,
                    $order->user_id,
                    (float) $order->total,
                    $order->status,
                    $order->user->email
                );

                Log::channel('activity')->info(
                    '[ORDER PROCESSING] Completed',
                    [
                        'user_id' => $this->userId,
                        'order_id' => $order->id,
                        'total' => $order->total,
                    ]
                );
            }, function () {

                Log::channel('activity')->warning(
                    '[ORDER PROCESSING] Delayed',
                    [
                        'user_id' => $this->userId,
                    ]
                );

                return $this->release(30);
            });
    }

    public function failed(
        Throwable $exception
    ): void {

        Log::channel('activity')->error(
            '[ORDER PROCESSING] Job failed permanently',
            [
                'user_id' => $this->userId,
                'exception' => $exception->getMessage(),
            ]
        );
    }
}
