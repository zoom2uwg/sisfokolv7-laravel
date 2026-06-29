<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama'     => 'Administrator',
                'email'    => 'admin@sisfokol.test',
                'password' => Hash::make('password'),
                'aktif'    => true,
                'tipe'     => 'admin_sekolah',
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
