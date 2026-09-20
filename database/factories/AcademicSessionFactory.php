<?php

namespace Database\Factories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicSessionFactory extends Factory
{
    public function definition(): array
    {
        $year = $this->faker->numberBetween(2024, 2026);
        $nextYear = $year + 1;

        return [
            'name' => "{$year}-{$nextYear}",
            'starts_at' => "$year-07-01",
            'ends_at' => $nextYear.'-06-30',
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }

    public function current(): static
    {
        return $this->state(function (): array {
            $now = CarbonImmutable::now()->startOfYear();

            return [
                'name' => $now->format('Y').'-'.$now->addYear()->format('Y'),
                'starts_at' => $now->format('Y-m-d'),
                'ends_at' => $now->addDays(360)->format('Y-m-d'),
                'is_active' => true,
            ];
        });
    }
}
