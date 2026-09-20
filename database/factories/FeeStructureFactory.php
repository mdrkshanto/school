<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeStructureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => AcademicSession::factory()->active(),
            'class_id' => SchoolClass::factory(),
            'name' => $this->faker->randomElement(['Tuition Fee', 'Admission Fee', 'Exam Fee', 'Transport Fee']),
            'type' => $this->faker->randomElement(['monthly', 'admission', 'one_time', 'transport']),
            'amount' => $this->faker->randomFloat(2, 500, 5000),
            'is_optional' => false,
            'status' => 'active',
        ];
    }
}