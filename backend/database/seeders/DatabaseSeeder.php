<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Danny Setiawan',
                'password' => Hash::make('password123'),
            ]
        );

        Item::firstOrCreate(
            ['name' => 'Laptop ASUS ROG', 'user_id' => $user->id],
            [
                'description' => 'Laptop gaming performa tinggi',
                'price' => 18500000,
                'stock' => 10,
            ]
        );

        Item::firstOrCreate(
            ['name' => 'Mechanical Keyboard RGB', 'user_id' => $user->id],
            [
                'description' => 'Keyboard mekanikal switch brown',
                'price' => 850000,
                'stock' => 25,
            ]
        );
    }
}