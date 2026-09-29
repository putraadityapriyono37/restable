<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\TableModel;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffPelunasanTest extends TestCase
{
    use RefreshDatabase;

    private function confirmedReservation(): array
    {
        $staff = User::factory()->create()->assignRole('staff');
        $owner = User::factory()->create()->assignRole('admin');
        $restaurant = Restaurant::factory()->create(['owner_id' => $owner->id]);
        $table = TableModel::factory()->create(['restaurant_id' => $restaurant->id]);
        $customer = User::factory()->create()->assignRole('customer');

        $reservation = Reservation::factory()->confirmed()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'table_id' => $table->id,
            'estimated_total' => 200000,
        ]);

        Transaction::factory()->settled()->create([
            'reservation_id' => $reservation->id,
            'type' => 'dp',
            'amount' => 60000,
        ]);

        return [$staff, $reservation];
    }

    public function test_staff_can_input_cash_pelunasan(): void
    {
        [$staff, $reservation] = $this->confirmedReservation();

        $response = $this->actingAs($staff)->post(route('staff.pelunasan'), [
            'reservation_id' => $reservation->id,
            'amount' => 140000,
            'payment_method' => 'cash',
            'confirm_cash' => 1,
        ]);

        $response->assertRedirect(route('staff.reservations'));
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('transactions', [
            'reservation_id' => $reservation->id,
            'type' => 'pelunasan',
            'payment_method' => 'cash',
            'status' => 'settlement',
        ]);
    }

    public function test_pelunasan_below_remaining_balance_is_rejected(): void
    {
        [$staff, $reservation] = $this->confirmedReservation();

        $response = $this->actingAs($staff)->post(route('staff.pelunasan'), [
            'reservation_id' => $reservation->id,
            'amount' => 100000,
            'payment_method' => 'cash',
            'confirm_cash' => 1,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_cash_pelunasan_requires_confirmation(): void
    {
        [$staff, $reservation] = $this->confirmedReservation();

        $response = $this->actingAs($staff)->post(route('staff.pelunasan'), [
            'reservation_id' => $reservation->id,
            'amount' => 140000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('confirm_cash');
    }

    public function test_customer_cannot_input_pelunasan(): void
    {
        $customer = User::factory()->create()->assignRole('customer');

        $response = $this->actingAs($customer)->post(route('staff.pelunasan'), [
            'reservation_id' => 999,
            'amount' => 140000,
            'payment_method' => 'cash',
            'confirm_cash' => 1,
        ]);

        $response->assertForbidden();
    }
}
