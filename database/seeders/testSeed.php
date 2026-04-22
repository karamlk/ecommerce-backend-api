<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class testSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Prepare the Product
        $product = Product::factory()->create(
            [
                'id' => 1,
                'name' => 'Demo Product',
                'stock' => 1,
                'price' => 100
            ]
        );

        // Prepare the Users
        User::factory()->create(['id' => 1, 'first_name' => 'User A', 'email' => 'a@test.com', 'password' => bcrypt('password')]);
        User::factory()->create(['id' => 2, 'first_name' => 'User B', 'email' => 'b@test.com', 'password' => bcrypt('password')]);

        // Prepare the Carts
        CartItem::factory()->create(['user_id' => 1, 'product_id' => 1, 'quantity' => 1]);
        CartItem::factory()->create(['user_id' => 2, 'product_id' => 1, 'quantity' => 1]);
    }
}
