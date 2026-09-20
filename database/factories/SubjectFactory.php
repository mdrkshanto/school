<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        $subjects = ['Mathematics', 'English', 'Science', 'Bangla', 'Social Studies', 'ICT', 'Physics', 'Chemistry', 'Biology', 'Art'];

        return [
            'name' => $this->faker->randomElement($subjects).' '.$this->faker->randomNumber(2),
            'code' => strtoupper($this->faker->unique()->lexify('??-###')),
            'description' => $this->faker->sentence(),
            'status' => 'active',
        ];
    }
}