<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\TableModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTableTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create()->assignRole('admin');
        Restaurant::factory()->create(['owner_id' => $admin->id]);

        return $admin;
    }

    public function test_admin_can_create_table(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.tables.store'), [
            'table_number' => 'A1',
            'capacity' => 4,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.tables.index'));
        $this->assertDatabaseHas('tables', [
            'table_number' => 'A1',
            'capacity' => 4,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_table(): void
    {
        $admin = $this->admin();
        $restaurant = Restaurant::where('owner_id', $admin->id)->first();
        $table = TableModel::factory()->create(['restaurant_id' => $restaurant->id]);

        $response = $this->actingAs($admin)->put(route('admin.tables.update', $table), [
            'table_number' => 'B2',
            'capacity' => 6,
            'is_active' => 0,
        ]);

        $response->assertRedirect(route('admin.tables.index'));
        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'table_number' => 'B2',
            'capacity' => 6,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_table(): void
    {
        $admin = $this->admin();
        $restaurant = Restaurant::where('owner_id', $admin->id)->first();
        $table = TableModel::factory()->create(['restaurant_id' => $restaurant->id]);

        $response = $this->actingAs($admin)->delete(route('admin.tables.destroy', $table));

        $response->assertRedirect(route('admin.tables.index'));
        $this->assertDatabaseMissing('tables', ['id' => $table->id]);
    }

    public function test_non_admin_cannot_manage_tables(): void
    {
        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)->get(route('admin.tables.index'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.tables.store'), [
            'table_number' => 'A1',
            'capacity' => 4,
        ])->assertForbidden();
    }
}
