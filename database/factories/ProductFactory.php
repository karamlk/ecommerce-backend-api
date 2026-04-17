<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(), 
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 2, 1000),
            'description' => $this->faker->sentence(12),
            'stock' => $this->faker->numberBetween(0, 200),
            'photo_url' => null,
        ];
    }
}