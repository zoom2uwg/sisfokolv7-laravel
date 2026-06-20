<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure super_admin role exists
        $role = Role::findOrCreate('super_admin', 'web');

        $superadmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'nama' => 'Super Administrator',
                'email' => 'superadmin@sisfokol.test',
                'password' => Hash::make('SuperAdmin#2026'),
                'aktif' => true,
                'tipe' => 'super_admin',
                'tenant_id' => null,
            ]
        );

        $superadmin->assignRole('super_admin');
    }
}
