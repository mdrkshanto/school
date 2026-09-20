<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Grade '.$this->faker->numberBetween(1, 12),
            'numeric_value' => $this->faker->numberBetween(1, 12),
            'description' => $this->faker->sentence(),
            'status' => 'active',
        ];
    }
}