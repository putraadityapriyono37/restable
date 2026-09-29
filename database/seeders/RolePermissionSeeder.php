<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'kelola-restoran',
            'lihat-laporan-keuangan',
            'input-pelunasan-cash',
            'lihat-reservasi-aktif',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($permissions);

        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->givePermissionTo(['input-pelunasan-cash', 'lihat-reservasi-aktif']);

        Role::firstOrCreate(['name' => 'customer']);
    }
}
