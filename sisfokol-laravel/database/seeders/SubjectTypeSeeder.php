<?php

namespace Database\Seeders;

use App\Models\SubjectType;
use Illuminate\Database\Seeder;

class SubjectTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'UMUM', 'name' => 'Mata Pelajaran Umum'],
            ['code' => 'KEJURUAN', 'name' => 'Mata Pelajaran Kejuruan'],
            ['code' => 'AGAMA', 'name' => 'Pendidikan Agama'],
            ['code' => 'PAKET', 'name' => 'Paket Keahlian'],
        ];

        foreach ($types as $type) {
            SubjectType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
