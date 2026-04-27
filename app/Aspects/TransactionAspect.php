<?php

namespace App\Aspects;

use Illuminate\Support\Facades\DB;

class TransactionAspect
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
