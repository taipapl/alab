<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 3 orders with unique order numbers and source for existing users
        $users = \App\Models\User::limit(3)->get();
        foreach ($users as $user) {
            Order::factory()
                ->create([
                    'patient_id' => $user->id,
                    'order_number' => Str::uuid()->toString(),
                    'source' => 'user',
                ]);
        }
    }
}