<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GradeRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['A', 'A-', 'B', 'C', 'D', 'F']),
            'grade_point' => $this->faker->randomFloat(1, 0, 5),
            'min_mark' => 80,
            'max_mark' => 100,
            'remark' => $this->faker->word(),
        ];
    }
}