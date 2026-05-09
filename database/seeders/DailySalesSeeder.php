<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

// Task 4
class DailySalesSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::factory(20)->create();
        $users    = User::factory(10)->create();

        // today completed 
        Order::factory(250)
            ->state([
                'status'     => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->recycle($users)
            ->recycle($products)
            ->has(
                OrderItem::factory()
                    ->count(3)
                    ->recycle($products),
                'items'
            )
            ->create();

        // pending
        Order::factory(20)
            ->state([
                'status'     => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->recycle($users)
            ->recycle($products)
            ->has(
                OrderItem::factory()
                    ->count(2)
                    ->recycle($products),
                'items'
            )
            ->create();

        // yesterday completed
        Order::factory(25)
            ->state([
                'status'     => 'completed',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ])
            ->recycle($users)
            ->recycle($products)
            ->has(
                OrderItem::factory()
                    ->count(2)
                    ->recycle($products),
                'items'
            )
            ->create();
    }
}