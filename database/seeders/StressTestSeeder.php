<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StressTestSeeder extends Seeder
{
    public function run(): void
    {
        // $store = Store::where('name', "McDonald's")->first();

        // // 2- stress testing one multiable products
        // $products = Product::inRandomOrder()->take(10)->get();
        // $hashedPassword = Hash::make('password123');

        // for ($i = 1; $i <= 100; $i++) {

        //     // 2- stress test on multiable products
        //     $product = $products[($i - 1) % 10]; // rotate through 10 products

        //     $user = User::firstOrCreate(
        //         ['email' => "stressuser{$i}@test.com"],
        //         [
        //             'first_name'    => 'Stress',
        //             'last_name'     => "User{$i}",
        //             'location'      => 'Damascus',
        //             'phone_number'  => '09' . str_pad($i, 8, '0', STR_PAD_LEFT),
        //             'email'         => "stressuser{$i}@test.com",
        //             'password'      => $hashedPassword,
        //             'profile_photo' => null,
        //         ]
        //     );

        //     // 2- stress test on multiable products
        //     CartItem::updateOrCreate(
        //         ['user_id' => $user->id, 'product_id' => $product->id],
        //         ['quantity' => 1]
        //     );
        // }

        /////////////////////////////////////////////////////////////////////

        // Task 9
        // stress test on more all the products
        $products = Product::all();
        $hashedPassword = Hash::make('password123');
        $cc = $products->count();
        if ($products->isEmpty()) {
            $this->command->error('No products found. Run main seeders first.');
            return;
        }

        for ($i = 1; $i <= 100; $i++) {
            $user = User::firstOrCreate(
                ['email' => "stressuser{$i}@test.com"],
                [
                    'first_name'    => 'Stress',
                    'last_name'     => "User{$i}",
                    'location'      => 'Damascus',
                    'phone_number'  => '09' . str_pad($i, 8, '0', STR_PAD_LEFT),
                    'password'      => $hashedPassword,
                    'profile_photo' => null,
                ]
            );

            $product = $products[($i - 1) % $cc];

            $product->update(['stock' => 500]);

            CartItem::updateOrCreate(
                [
                    'user_id'    => $user->id,
                    'product_id' => $product->id
                ],
                ['quantity'   => 1]
            );
        }

        ///////////////////////////////////////////////////////////////////////

        // $store = Store::where('name', "McDonald's")->first();

        // // 1- stress test on one product
        //  $product = Product::where('store_id', $store->id)
        //      ->where('name', 'Fries')
        //      ->first();

        // $hashedPassword = Hash::make('password123');
        // for ($i = 1; $i <= 100; $i++) {

        //     $user = User::firstOrCreate(
        //         ['email' => "stressuser{$i}@test.com"],
        //         [
        //             'first_name'    => 'Stress',
        //             'last_name'     => "User{$i}",
        //             'location'      => 'Damascus',
        //             'phone_number'  => '09' . str_pad($i, 8, '0', STR_PAD_LEFT),
        //             'email'         => "stressuser{$i}@test.com",
        //             'password'      => $hashedPassword ,
        //             'profile_photo' => null,
        //         ]
        //     );

        //     // 1- stress test on one product
        //      CartItem::updateOrCreate(
        //          [
        //              'user_id'    => $user->id,
        //              'product_id' => $product->id,
        //          ],
        //          ['quantity' => 1]
        //      );
        // }
    }
}
