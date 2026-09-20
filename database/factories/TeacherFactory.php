<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_no' => 'T-'.$this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'dob' => $this->faker->dateTimeBetween('-50 years', '-24 years')->format('Y-m-d'),
            'phone' => $this->faker->numerify('01#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'qualification' => $this->faker->randomElement(['BSc', 'MSc', 'M.Ed', 'PhD']),
            'specialization' => $this->faker->randomElement(['Mathematics', 'English', 'Science', 'Arts']),
            'address' => $this->faker->address(),
            'join_date' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}