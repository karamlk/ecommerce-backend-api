<?php

namespace App\Console\Commands;

use App\Services\Order\DailySalesService;
use Illuminate\Console\Command;

class ProcessDailySalesDemo extends Command
{
    protected $signature = 'sales:demo-before';
    protected $description = 'Run sales processing demo without batch (before)';

    public function handle(DailySalesService $service)
    {
        $date = now()->toDateString();

        $result = $service->processWithoutChunks($date);

        $this->info(json_encode($result, JSON_PRETTY_PRINT));
    }
}
