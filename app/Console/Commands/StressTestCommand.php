<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

// Task 9
class StressTestCommand extends Command
{
    protected $signature = 'init:stress';
    protected $description = 'Stress Testing Init';

    public function handle()
    {
        $this->info('Starting Stress Test Initialization...');

        $this->call('migrate:fresh', ['--force' => true]);

        $this->info('Seeding default data...');
        $this->call('db:seed', ['--force' => true]);

        $this->info('Seeding stress test specific data...');
        $this->call('db:seed', [
            '--class' => 'StressTestSeeder',
            '--force' => true
        ]);

        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('route:clear');
        $this->call('optimize');

        return Command::SUCCESS;
    }
}
