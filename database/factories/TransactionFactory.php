<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'type' => 'dp',
            'payment_method' => 'midtrans',
            'midtrans_order_id' => 'DP-'.fake()->unique()->numberBetween(10000, 99999),
            'amount' => 50000,
            'status' => 'pending',
            'snap_token' => fake()->uuid(),
            'recorded_by' => null,
            'raw_response' => null,
        ];
    }

    public function settled(): static
    {
        return $this->state(fn () => ['status' => 'settlement']);
    }

    public function cash(): static
    {
        return $this->state(fn () => [
            'payment_method' => 'cash',
            'midtrans_order_id' => null,
            'snap_token' => null,
            'status' => 'settlement',
        ]);
    }

    public function pelunasan(): static
    {
        return $this->state(fn () => ['type' => 'pelunasan']);
    }
}
