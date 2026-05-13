<?php

namespace App\Jobs;

use App\Services\Order\DailySalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSalesChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 10;

    public function __construct(
        public array  $orderIds,
        public int    $chunkNumber,
        public string $date,
    ) {
        $this->onQueue('sales');
    }

    public function handle(DailySalesService $service): void
    {
        $startMemory = memory_get_usage(true);
        $start       = microtime(true);

        Log::channel('activity')->info("[DAILY SALES] Chunk {$this->chunkNumber} started", [
            'orders_in_chunk' => count($this->orderIds),
            'date'            => $this->date,
        ]);

        $result = $service->processChunk($this->orderIds, $this->chunkNumber, $this->date);

    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('activity')->error("[DAILY SALES] Chunk {$this->chunkNumber} failed", [
            'date'      => $this->date,
            'exception' => $exception->getMessage(),
        ]);
    }
}
