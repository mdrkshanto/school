<?php

namespace Database\Factories;

use App\Models\FeeInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeePaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => FeeInvoice::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'method' => $this->faker->randomElement(['cash', 'bank', 'card', 'online', 'cheque']),
            'transaction_id' => $this->faker->optional()->numerify('TXN#######'),
            'paid_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}