<?php

namespace Tests\Feature;

use App\Jobs\CancelExpiredReservation;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\TableModel;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReservationStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    private function restaurantWithTable(User $owner): Restaurant
    {
        $restaurant = Restaurant::factory()->create([
            'owner_id' => $owner->id,
            'open_time' => '10:00:00',
            'close_time' => '22:00:00',
            'slot_duration' => 60,
            'dp_type' => 'percentage',
            'dp_value' => 30,
        ]);

        TableModel::factory()->create([
            'restaurant_id' => $restaurant->id,
            'capacity' => 4,
            'is_active' => true,
        ]);

        return $restaurant;
    }

    public function test_customer_can_create_reservation(): void
    {
        $owner = User::factory()->create()->assignRole('admin');
        $restaurant = $this->restaurantWithTable($owner);
        $customer = User::factory()->create()->assignRole('customer');

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createSnapToken')->andReturn('snap-token-test');
        });

        $response = $this->actingAs($customer)->post(route('reservations.store'), [
            'restaurant_id' => $restaurant->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'reservation_time' => '18:00',
            'guest_count' => 2,
            'estimated_total' => 200000,
            'notes' => 'Meja dekat jendela',
        ]);

        $reservation = Reservation::firstOrFail();

        $response->assertRedirect(route('reservations.pay', $reservation));

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'user_id' => $customer->id,
            'status' => 'waiting_payment',
        ]);
        $this->assertDatabaseHas('transactions', [
            'reservation_id' => $reservation->id,
            'type' => 'dp',
            'status' => 'pending',
            'payment_method' => 'midtrans',
        ]);

        Queue::assertPushed(CancelExpiredReservation::class);
    }

    public function test_reservation_outside_operating_hours_is_rejected(): void
    {
        $owner = User::factory()->create()->assignRole('admin');
        $restaurant = $this->restaurantWithTable($owner);
        $customer = User::factory()->create()->assignRole('customer');

        $response = $this->actingAs($customer)->post(route('reservations.store'), [
            'restaurant_id' => $restaurant->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'reservation_time' => '23:00',
            'guest_count' => 2,
            'estimated_total' => 200000,
        ]);

        $response->assertSessionHasErrors('reservation_time');
        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_reservation_without_available_table_is_rejected(): void
    {
        $owner = User::factory()->create()->assignRole('admin');
        $restaurant = Restaurant::factory()->create([
            'owner_id' => $owner->id,
            'open_time' => '10:00:00',
            'close_time' => '22:00:00',
            'slot_duration' => 60,
        ]);
        $customer = User::factory()->create()->assignRole('customer');

        $response = $this->actingAs($customer)->post(route('reservations.store'), [
            'restaurant_id' => $restaurant->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'reservation_time' => '18:00',
            'guest_count' => 2,
            'estimated_total' => 200000,
        ]);

        $response->assertSessionHasErrors('reservation_time');
        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_guest_cannot_create_reservation(): void
    {
        $owner = User::factory()->create()->assignRole('admin');
        $restaurant = $this->restaurantWithTable($owner);

        $response = $this->post(route('reservations.store'), [
            'restaurant_id' => $restaurant->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'reservation_time' => '18:00',
            'guest_count' => 2,
            'estimated_total' => 200000,
        ]);

        $response->assertRedirect(route('login'));
    }
}
