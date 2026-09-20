<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 month', 'now');

        return [
            'session_id' => AcademicSession::factory()->active(),
            'name' => $this->faker->randomElement(['First Term', 'Midterm', 'Final Exam']),
            'description' => $this->faker->sentence(),
            'starts_at' => $start,
            'ends_at' => (clone $start)->modify('+14 days'),
            'status' => 'published',
        ];
    }
}