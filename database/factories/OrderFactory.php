<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => \App\Models\User::factory(),
            'order_number' => Str::uuid()->toString(),
            'source' => $this->faker->optional()->randomElement(['user', 'import']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
