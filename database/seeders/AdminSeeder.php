<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@ecom.com'],
            [
                'first_name'   => 'Admin',
                'last_name'    => 'User',
                'location'     => 'Damascus',
                'phone_number' => '0988888888',
                'email'        => 'admin@ecom.com',
                'password'     => Hash::make('password'),
                'role'         => 'admin',
            ]
        );

        // for optimistic testing
        //    User::firstOrCreate(
        //     ['email' => 'admin1@ecom.com'],
        //     [
        //         'first_name'   => 'Admin1',
        //         'last_name'    => 'User',
        //         'location'     => 'Damascus',
        //         'phone_number' => '0988886888',
        //         'email'        => 'admin1@ecom.com',
        //         'password'     => Hash::make('password'),
        //         'role'         => 'admin',
        //     ]
        // );

        //    $product = Product::factory()->create(
        //     [
        //         'id' => 1,
        //         'name' => 'Demo Product',
        //         'stock' => 1,
        //         'price' => 100
        //     ]
        // );
    }
}
