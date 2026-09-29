<?php

namespace Tests\Feature;

use App\Jobs\CancelExpiredReservation;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelExpiredReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiting_payment_reservation_is_cancelled(): void
    {
        $user = User::factory()->create()->assignRole('customer');
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'status' => 'waiting_payment',
        ]);
        $transaction = Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => 'dp',
            'status' => 'pending',
        ]);

        (new CancelExpiredReservation($reservation->id))->handle();

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'expire',
        ]);
        $this->assertDatabaseHas('reservation_logs', [
            'reservation_id' => $reservation->id,
            'status_from' => 'waiting_payment',
            'status_to' => 'cancelled',
        ]);
    }

    public function test_confirmed_reservation_is_not_cancelled(): void
    {
        $user = User::factory()->create()->assignRole('customer');
        $reservation = Reservation::factory()->confirmed()->create([
            'user_id' => $user->id,
        ]);

        (new CancelExpiredReservation($reservation->id))->handle();

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed',
        ]);
    }
}
