<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        if (! $academicYear) {
            return;
        }

        $classrooms = [
            ['name' => 'X IPA 1', 'level' => 'X', 'major' => 'IPA', 'capacity' => 32],
            ['name' => 'X IPA 2', 'level' => 'X', 'major' => 'IPA', 'capacity' => 32],
            ['name' => 'X IPS 1', 'level' => 'X', 'major' => 'IPS', 'capacity' => 32],
            ['name' => 'XI IPA 1', 'level' => 'XI', 'major' => 'IPA', 'capacity' => 32],
            ['name' => 'XI IPS 1', 'level' => 'XI', 'major' => 'IPS', 'capacity' => 32],
            ['name' => 'XII IPA 1', 'level' => 'XII', 'major' => 'IPA', 'capacity' => 32],
            ['name' => 'XII IPS 1', 'level' => 'XII', 'major' => 'IPS', 'capacity' => 32],
        ];

        foreach ($classrooms as $classroom) {
            Classroom::firstOrCreate(
                ['name' => $classroom['name'], 'academic_year_id' => $academicYear->id],
                array_merge($classroom, ['academic_year_id' => $academicYear->id])
            );
        }
    }
}
