<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'class_id' => SchoolClass::factory(),
            'student_id' => Student::factory(),
            'subject_id' => Subject::factory(),
            'obtained_marks' => $this->faker->randomFloat(2, 30, 100),
            'is_absent' => false,
        ];
    }

    public function absent(): static
    {
        return $this->state(fn () => ['obtained_marks' => null, 'is_absent' => true, 'grade' => null, 'grade_point' => null]);
    }
}