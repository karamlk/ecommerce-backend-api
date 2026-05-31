<?php

namespace App\Aspects;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Task 8: ACID Transaction
class TransactionAspect
{
    public function run(callable $callback)
    {
        return DB::transaction(function () use ($callback) {

            Log::channel('activity')->info(
                '[TRANSACTION] Started'
            );

            try {

                $result = $callback();

                Log::channel('activity')->info(
                    '[TRANSACTION] Committed'
                );

                return $result;
            } catch (\Throwable $e) {

                Log::channel('activity')->error(
                    '[TRANSACTION] Rolled back',
                    [
                        'error' => $e->getMessage(),
                    ]
                );

                throw $e;
            }
        });
    }
}
