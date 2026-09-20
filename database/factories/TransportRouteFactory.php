<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TransportRouteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Route '.$this->faker->randomElement(['A', 'B', 'C', 'North', 'South']),
            'description' => $this->faker->sentence(),
            'status' => 'active',
        ];
    }
}