<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::firstOrCreate(
            ['name' => '2025/2026'],
            [
                'start_date'  => '2025-07-01',
                'end_date'    => '2026-06-30',
                'is_active'   => true,
                'description' => 'Tahun Pelajaran Default',
            ]
        );
    }
}
