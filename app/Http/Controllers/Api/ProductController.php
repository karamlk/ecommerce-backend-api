<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\Product\ProductService;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function index(int $storeId)
    {
        return ProductResource::collection(
            $this->productService->getStoreProducts((int) $storeId)
        );
    }

    public function show(int $storeId, int $productId)
    {
        return new ProductResource(
            $this->productService->getProduct((int) $storeId, (int) $productId)
        );
    }

    public function home()
    {
        return ProductResource::collection(
            $this->productService->getHomeProducts()
        );
    }

    public function trending()
    {
        return ProductResource::collection(
            $this->productService->getTrendingProducts()
        );
    }
}
