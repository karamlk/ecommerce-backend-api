<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    public function created(Product $product): void
    {
        Log::channel('activity')->info('[PRODUCT CREATED]', [
            'product_id' => $product->id,
            'name'       => $product->name,
            'store_id'   => $product->store_id,
        ]);
    }

    public function updated(Product $product): void
    {
        Log::channel('activity')->info('[PRODUCT UPDATED]', [
            'product_id' => $product->id,
            'changed'    => array_keys($product->getDirty()),
        ]);
    }
}
