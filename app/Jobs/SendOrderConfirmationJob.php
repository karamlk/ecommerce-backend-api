<?php

namespace App\Jobs;

use App\Mail\OrderConfirmationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public int    $orderId,
        public int    $userId,
        public float  $total,
        public string $status,
        public string $email,
    ) {}

    public function handle(): void
    {
        // TASK 3: Asynchronous Queues
        // TASK 2: Resource Management & Capacity Control - throttle emails-jobs
        Redis::throttle('send-order-confirmation')
            ->allow(20)
            ->every(60)
            ->then(function () {

                Log::channel('activity')->info('[ORDER CONFIRMATION] Sending email', [
                    'order_id' => $this->orderId,
                    'user_id'  => $this->userId,
                    'email'    => $this->email,
                    'total'    => $this->total,
                ]);

                Mail::to($this->email)
                    ->send(new OrderConfirmationMail(
                        $this->orderId,
                        $this->total,
                        $this->status,
                    ));

                Log::channel('activity')->info('[ORDER CONFIRMATION] Email sent', [
                    'order_id' => $this->orderId,
                    'email'    => $this->email,
                ]);
            }, function () {
                Log::channel('activity')->warning('[ORDER CONFIRMATION] Delayed', [
                    'order_id' => $this->orderId,
                ]);
                return $this->release(30);
            });
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('activity')->error('[ORDER CONFIRMATION] Job failed permanently', [
            'order_id'  => $this->orderId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
