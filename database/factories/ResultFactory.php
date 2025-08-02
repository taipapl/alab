<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Result>
 */
class ResultFactory extends Factory
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
            'order_id' => \App\Models\Order::factory(),
            'test_name' => $this->faker->word(),
            'test_value' => $this->faker->randomFloat(2, 1, 100),
            'test_reference' => $this->faker->word(),
        ];
    }
}
