<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'class_id' => SchoolClass::factory(),
            'subject_id' => Subject::factory(),
            'date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'full_marks' => 100,
            'pass_marks' => 33,
        ];
    }
}