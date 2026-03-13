<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Invoice;
use App\Enums\InvoiceStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->randomFloat(2, 50, 5000);
        return [
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'patient_id' => \App\Models\Patient::factory(),
            'doctor_id' => \App\Models\User::factory(),
            'total_amount' => $total,
            'paid_amount' => 0,
            'balance_due' => $total,
            'status' => InvoiceStatus::UNPAID,
            'is_finalized' => false,
            'due_date' => now()->addDays(7),
        ];
    }
}
