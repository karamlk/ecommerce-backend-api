<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDailySalesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

// Task 4
class ProcessDailySalesCommand extends Command
{
    protected $signature   = 'sales:process-daily';
    protected $description = 'Process daily sales report in chunks';

    public function handle(): void
    {
        $this->info('[' . now() . '] Dispatching daily sales job...');

        ProcessDailySalesJob::dispatch();

        Log::channel('activity')->info('[SCHEDULER] Daily sales job dispatched', [
            'timestamp' => now()->toDateTimeString(),
        ]);

        $this->info('Job dispatched to sales queue.');
    }
}