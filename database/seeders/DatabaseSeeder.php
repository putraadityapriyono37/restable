<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\TableModel;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        $admin = User::firstWhere('email', 'admin@restable.test')
            ?? User::factory()->create([
                'name' => 'Admin Resto',
                'email' => 'admin@restable.test',
            ]);

        $admin->assignRole('admin');

        $restaurant = Restaurant::firstOrCreate(
            ['owner_id' => $admin->id],
            [
                'name' => 'Warung Contoh',
                'address' => 'Jl. Contoh No. 1',
                'phone' => '081234567890',
                'open_time' => '10:00:00',
                'close_time' => '22:00:00',
                'slot_duration' => 60,
                'dp_type' => 'percentage',
                'dp_value' => 30,
            ]
        );

        if ($restaurant->tables()->count() === 0) {
            foreach (range(1, 8) as $number) {
                TableModel::create([
                    'restaurant_id' => $restaurant->id,
                    'table_number' => (string) $number,
                    'capacity' => $number <= 4 ? 2 : ($number <= 6 ? 4 : 6),
                    'is_active' => true,
                ]);
            }
        }
    }
}
