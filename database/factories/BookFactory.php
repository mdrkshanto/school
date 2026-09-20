<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->unique()->isbn13(),
            'publisher' => $this->faker->company(),
            'category' => $this->faker->randomElement(['Fiction', 'Science', 'History', 'Poetry', 'Reference']),
            'quantity' => $q = $this->faker->numberBetween(1, 10),
            'available_count' => $this->faker->numberBetween(0, $q),
            'status' => 'active',
        ];
    }
}