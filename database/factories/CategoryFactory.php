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
                'Toys',
                'Automotive',
                'Gaming',
                'Pet Supplies',
                'Office Supplies',
                'Garden',
                'Jewelry',
                'Shoes',
                'Accessories',
                'Music',
                'Movies',
                'Baby Products',
                'Kitchen',
                'Groceries',
                'Stationery',
                'Travel',
                'Fitness',
                'Outdoor',
                'Smart Devices',
                'Phones',
                'Laptops',
                'Cameras',
                'Watches',
                'Perfumes',
                'Skincare',
                'Bakery',
                'Beverages',
                'Snacks',
                'Medical Supplies',
                'Tools',
                'Art Supplies',
                'Lighting',
                'Decor',
                'Cleaning Supplies',
                'Industrial',
                'Hardware',
                'Streaming Devices',
                'Board Games',
                'Supplements',
                'Cycling',
            ]),
        ];
    }
}