<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'registration_no' => strtoupper($this->faker->unique()->bothify('##-####')),
            'model' => $this->faker->randomElement(['Toyota HiAce', 'Mitsubishi Rosa', 'Nissan Civilian', 'BUS 45']),
            'capacity' => $this->faker->randomElement([20, 30, 45]),
            'driver_name' => $this->faker->name(),
            'driver_phone' => $this->faker->numerify('01#########'),
            'status' => 'active',
        ];
    }
}