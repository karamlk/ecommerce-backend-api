<?php

namespace Database\Factories;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    
    protected $model = CartItem::class;

    public function definition(): array
    {
        
      return [
            'user_id' => User::factory(), 
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 10), 
        ];
    }
}
