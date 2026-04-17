<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'category_id' => Category::factory(),
            'photo_url' => null,
        ];
    }
}