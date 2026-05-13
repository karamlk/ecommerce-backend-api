<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class testQueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // multiable users
        $product = Product::firstOrCreate(
            ['name' => 'Demo Product'],
            Product::factory()->raw([
                'stock' => 50,
                'price' => 100,
            ])
        );

        $user1 = User::firstOrCreate(
            ['email' => 'john1@example.com'],
            [
                'first_name'    => 'Benchmark0',
                'last_name'     => 'User0',
                'location'      => 'Damascus',
                'phone_number'  => '0999999999',
                'email'         => 'john1@example.com',
                'password'      => Hash::make('password'),
                'profile_photo' => null,
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'john2@example.com'],
            [
                'first_name'    => 'Benchmark1',
                'last_name'     => 'User1',
                'location'      => 'Damascus',
                'phone_number'  => '0999999998',
                'email'         => 'john2@example.com',
                'password'      => Hash::make('password'),
                'profile_photo' => null,
            ]
        );

        $user3 = User::firstOrCreate(
            ['email' => 'john3@example.com'],
            [
                'first_name'    => 'Benchmark2',
                'last_name'     => 'User2',
                'location'      => 'Damascus',
                'phone_number'  => '0999999997',
                'email'         => 'john3@example.com',
                'password'      => Hash::make('password'),
                'profile_photo' => null,
            ]
        );

        $users = collect([
            $user1,
            $user2,
            $user3
        ]);

        foreach ($users as $user) {

            CartItem::updateOrCreate(
                [
                    'user_id'    => $user->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => 1,
                ]
            );
        }
    }
}
