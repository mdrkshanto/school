<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookIssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'user_id' => User::factory(),
            'issued_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'status' => 'issued',
        ];
    }

    public function returned(): static
    {
        return $this->state(fn () => ['returned_at' => now(), 'status' => 'returned']);
    }
}