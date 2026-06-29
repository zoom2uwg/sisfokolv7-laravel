<?php

namespace Database\Seeders;

use App\Models\SchoolProfile;
use Illuminate\Database\Seeder;

class SchoolProfileSeeder extends Seeder
{
    public function run(): void
    {
        SchoolProfile::firstOrCreate(
            ['name' => env('SCHOOL_NAME', 'Sekolah BiasaWae')],
            [
                'address'         => env('SCHOOL_ADDRESS', 'Jl. Raya...'),
                'city'            => env('SCHOOL_CITY', 'Kendal'),
                'phone'           => env('SCHOOL_PHONE', '0818298854'),
                'email'           => null,
                'npsn'            => null,
                'nss'             => null,
                'headmaster_name' => null,
                'headmaster_nip'  => null,
            ]
        );
    }
}
