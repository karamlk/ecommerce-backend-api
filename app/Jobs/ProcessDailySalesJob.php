<?php

namespace App\Jobs;

use App\Models\Order;
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

    public function handle(): void
    {
        $date        = now()->toDateString();
        $chunkNumber = 0;

        Log::channel('activity')->info('[DAILY SALES] Orchestrator started', [
            'date' => $date,
        ]);

        Order::where('status', 'completed')
            ->whereDate('created_at', $date)
            ->select('id')
            ->chunkById(100, function ($orders) use (&$chunkNumber, $date) {
                $chunkNumber++;

                ProcessSalesChunkJob::dispatch(
                    $orders->pluck('id')->toArray(),
                    $chunkNumber,
                    $date,
                );

                Log::channel('activity')->info("[DAILY SALES] Chunk {$chunkNumber} dispatched", [
                    'orders' => $orders->count(),
                    'date'   => $date,
                ]);
            });

        Log::channel('activity')->info('[DAILY SALES] All chunks dispatched', [
            'total_chunks' => $chunkNumber,
            'date'         => $date,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('activity')->error('[DAILY SALES] Job failed permanently', [
            'date'      => now()->toDateString(),
            'exception' => $exception->getMessage(),
        ]);
    }
}
