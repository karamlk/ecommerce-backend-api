<?php

namespace App\Services\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Task 5: load balancing
class LoadBalancerService
{
    private array $servers = [

        [
            'name'   => 'server-1',
            'url'    => 'http://127.0.0.1:8001',
            'weight' => 5,
        ],

        [
            'name'   => 'server-2',
            'url'    => 'http://127.0.0.1:8002',
            'weight' => 3,
        ],

        [
            'name'   => 'server-3',
            'url'    => 'http://127.0.0.1:8003',
            'weight' => 1,
        ],
    ];

    private function weightedPool(): array
    {
        $pool = [];

        foreach ($this->servers as $server) {

            for ($i = 0; $i < $server['weight']; $i++) {
                $pool[] = $server;
            }
        }

        return $pool;
    }

    public function nextServer(): array
    {
        $pool  = $this->weightedPool();
        $count = count($pool);

        $index = Cache::increment(
            'load_balancer:index'
        );

        return $pool[
            ($index - 1) % $count
        ];
    }

    public function forward(string $path): array
    {
        $server = $this->nextServer();

        $url = $server['url'] . $path;

        $start = microtime(true);

        $response = Http::timeout(5)->get($url);

        $duration = round(
            (microtime(true) - $start) * 1000,
            2
        );

        Log::channel('activity')->info(
            '[LOAD BALANCER] Request routed',
            [
                'server'      => $server['name'],
                'weight'      => $server['weight'],
                'url'         => $path,
                'duration_ms' => $duration,
                'strategy'    => 'Weighted Round Robin',
            ]
        );

        return [
            'server'      => $server['name'],
            'weight'      => $server['weight'],
            'status'      => $response->status(),
            'duration_ms' => $duration,
        ];
    }
}