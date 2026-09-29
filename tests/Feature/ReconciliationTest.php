<?php

namespace Tests\Feature;

use App\Models\CashReconciliation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create()->assignRole('admin');
        Restaurant::factory()->create(['owner_id' => $admin->id]);

        return $admin;
    }

    public function test_admin_can_store_reconciliation(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create()->assignRole('staff');

        $response = $this->actingAs($admin)->post(route('admin.reconciliation.store'), [
            'date' => now()->toDateString(),
            'total_cash_physical' => 0,
            'staff_id' => $staff->id,
        ]);

        $response->assertRedirect(route('admin.reconciliation', ['date' => now()->toDateString()]));
        $this->assertDatabaseHas('cash_reconciliations', [
            'confirmed_by' => $admin->id,
        ]);
    }

    public function test_reconciliation_with_selisih_requires_note(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create()->assignRole('staff');

        $response = $this->actingAs($admin)->post(route('admin.reconciliation.store'), [
            'date' => now()->toDateString(),
            'total_cash_physical' => 100000,
            'staff_id' => $staff->id,
        ]);

        $response->assertSessionHasErrors('note');
        $this->assertDatabaseCount('cash_reconciliations', 0);
    }

    public function test_reconciliation_already_confirmed_is_rejected(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create()->assignRole('staff');
        $restaurant = Restaurant::where('owner_id', $admin->id)->first();

        CashReconciliation::create([
            'restaurant_id' => $restaurant->id,
            'staff_id' => $staff->id,
            'date' => now()->toDateString(),
            'total_cash_system' => 0,
            'total_cash_physical' => 0,
            'selisih' => 0,
            'confirmed_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reconciliation.store'), [
            'date' => now()->toDateString(),
            'total_cash_physical' => 0,
            'staff_id' => $staff->id,
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_non_admin_cannot_access_reconciliation(): void
    {
        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)->get(route('admin.reconciliation'))->assertForbidden();
    }
}
