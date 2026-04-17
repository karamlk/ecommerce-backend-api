<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Food',
                'Electronics',
                'Fashion',
                'Books',
                'Home Appliances',
                'Furniture',
                'Sports',
                'Health',
                'Beauty',
                'Toys'
            ]),
        ];
    }
}