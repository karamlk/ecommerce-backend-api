<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrderTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'first_name'    => 'Benchmark',
                'last_name'     => 'User',
                'location'      => 'Damascus',
                'phone_number'  => '0999999999',
                'email'         => 'john@example.com',
                'password'      => Hash::make('password'),
                'profile_photo' => null,
            ]
        );

        $product = Product::factory()->create(
            [
                'name' => 'Demo Product',
                'stock' => 1,
                'price' => 100
            ]
        );

        if (!$product) {
            $this->command->error('No products found. Please seed products first.');
            return;
        }

        $order = Order::create([
            'user_id' => $user->id,
            'status'  => 'pending',
            'total'   => $product->price * 2,
        ]);


        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 2,
            'price'      => $product->price,
        ]);
    }
}
