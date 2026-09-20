<?php

namespace Database\Factories;

use App\Models\TransportRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransportStopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'route_id' => TransportRoute::factory(),
            'name' => $this->faker->randomElement(['Garden Road', 'Main Gate', 'Central Market', 'Railway Station', 'City Center']),
            'fare' => $this->faker->randomFloat(2, 100, 1000),
            'order_index' => $this->faker->numberBetween(0, 10),
        ];
    }
}