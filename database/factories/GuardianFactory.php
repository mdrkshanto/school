<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GuardianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('01#########'),
            'occupation' => $this->faker->randomElement(['Business', 'Teacher', 'Engineer', 'Doctor', 'Farmer']),
            'address' => $this->faker->address(),
            'status' => 'active',
        ];
    }
}