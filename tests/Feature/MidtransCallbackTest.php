<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransCallbackTest extends TestCase
{
    use RefreshDatabase;

    private function signature(array $payload): string
    {
        $serverKey = config('services.midtrans.server_key');

        return hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey);
    }

    public function test_callback_rejects_invalid_signature(): void
    {
        $response = $this->postJson('/midtrans/callback', [
            'order_id' => 'DP-1-1',
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'signature_key' => 'invalid-signature',
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(403);
    }

    public function test_callback_settlement_confirms_reservation(): void
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
            'midtrans_order_id' => 'DP-'.$reservation->id.'-1',
            'amount' => 50000,
        ]);

        $payload = [
            'order_id' => $transaction->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = $this->signature($payload);

        $response = $this->postJson('/midtrans/callback', $payload);

        $response->assertOk();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed',
        ]);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'settlement',
        ]);
    }

    public function test_callback_is_idempotent(): void
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
            'midtrans_order_id' => 'DP-'.$reservation->id.'-1',
            'amount' => 50000,
        ]);

        $payload = [
            'order_id' => $transaction->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = $this->signature($payload);

        $this->postJson('/midtrans/callback', $payload)->assertOk();
        $this->postJson('/midtrans/callback', $payload)->assertOk()->assertJson([
            'message' => 'Already processed',
        ]);
    }
}
