<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use App\Models\FeeStructure;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeInvoiceFactory extends Factory
{
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 500, 5000);

        return [
            'session_id' => AcademicSession::factory()->active(),
            'student_id' => Student::factory(),
            'fee_structure_id' => FeeStructure::factory(),
            'invoice_no' => 'INV-'.$this->faker->unique()->numberBetween(1000, 99999),
            'amount' => $amount,
            'paid_amount' => 0,
            'due_date' => $this->faker->dateTimeBetween('now', '+60 days')->format('Y-m-d'),
            'status' => 'pending',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'paid',
            'paid_amount' => $attrs['amount'],
            'due_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
        ]);
    }
}