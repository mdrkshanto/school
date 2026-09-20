<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('now', '+60 days');

        return [
            'title' => $this->faker->randomElement(['Parents Day', 'Sports Day', 'Annual Function', 'Science Fair', 'Culture Week']),
            'description' => $this->faker->sentence(),
            'location' => $this->faker->randomElement(['Main Hall', 'Playground', 'Auditorium', 'Library']),
            'starts_at' => $start,
            'ends_at' => (clone $start)->modify('+4 hours'),
            'color' => $this->faker->randomElement(['indigo', 'emerald', 'rose', 'amber', 'sky']),
            'is_public' => true,
        ];
    }
}