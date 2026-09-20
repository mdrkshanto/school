<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentAttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => AcademicSession::factory()->active(),
            'class_id' => SchoolClass::factory(),
            'student_id' => Student::factory(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['present', 'present', 'present', 'absent', 'late']),
        ];
    }
}