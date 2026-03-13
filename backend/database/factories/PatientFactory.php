<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Patient;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(),
            'age' => fake()->numberBetween(1, 90),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other']),
            'address' => fake()->address(),
            'first_visit_date' => now(),
            'doctor_id' => \App\Models\User::factory(),
        ];
    }
}
