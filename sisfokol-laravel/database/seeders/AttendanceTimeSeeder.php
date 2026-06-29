<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AttendanceTime;
use Illuminate\Database\Seeder;

class AttendanceTimeSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        if (! $academicYear) {
            return;
        }

        AttendanceTime::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'type' => 'in'],
            [
                'start_time'  => '06:30:00',
                'end_time'    => '07:30:00',
                'is_active'   => true,
                'description' => 'Waktu presensi hadir',
            ]
        );

        AttendanceTime::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'type' => 'out'],
            [
                'start_time'  => '14:00:00',
                'end_time'    => '15:00:00',
                'is_active'   => true,
                'description' => 'Waktu presensi pulang',
            ]
        );
    }
}
