<?php

namespace App\Jobs;

use App\Services\Order\DailySalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

// TASK 4: Batch Processing
class ProcessDailySalesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 10;

    public function __construct()
    {
        $this->onQueue('sales');
    }

    public function handle(DailySalesService $service): void
    {
        $result = $service->process(now()->toDateString());

        Log::channel('activity')->info(
            '[DAILY SALES] Processing complete',
            $result
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('activity')->error('[DAILY SALES] Job failed permanently', [
            'date'      => now()->toDateString(),
            'exception' => $exception->getMessage(),
        ]);
    }
}
