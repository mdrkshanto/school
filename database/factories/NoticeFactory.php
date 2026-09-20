<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NoticeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(5),
            'body' => $this->faker->paragraphs(2, true),
            'category' => $this->faker->randomElement(['general', 'academic', 'urgent']),
            'audience' => $this->faker->randomElement(['all', 'students', 'teachers', 'parents']),
            'published_at' => $this->faker->dateTimeBetween('-14 days', 'now'),
        ];
    }
}