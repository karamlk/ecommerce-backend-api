<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    public function __construct(
        private ProductService $service
    ) {}

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
        $changed = array_keys($product->getChanges());

        $nonStructuralChanges = ['stock', 'updated_at'];

        if (empty(array_diff($changed, $nonStructuralChanges))) {

            Log::channel('activity')->info('[PRODUCT STOCK UPDATED]', [
                'product_id' => $product->id,
            ]);

            return;
        }

        $this->clear($product);

        Log::channel('activity')->info('[PRODUCT UPDATED]', [
            'product_id' => $product->id,
            'changed'    => $changed,
        ]);
    }

    // public function deleted(Product $product): void
    // {
    //     $this->clear($product);
    //     Log::channel('activity')->info('[PRODUCT DELETED]', [
    //         'product_id' => $product->id,
    //     ]);
    // }

    private function clear(Product $product): void
    {
        $this->service->invalidateProductCache(
            $product->store_id,
            $product->id
        );
    }
}
