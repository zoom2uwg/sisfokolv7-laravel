<?php

namespace Database\Seeders;

use App\Models\Day;
use Illuminate\Database\Seeder;

class DaySeeder extends Seeder
{
    public function run(): void
    {
        $days = [
            ['number' => 0, 'name' => 'Minggu'],
            ['number' => 1, 'name' => 'Senin'],
            ['number' => 2, 'name' => 'Selasa'],
            ['number' => 3, 'name' => 'Rabu'],
            ['number' => 4, 'name' => 'Kamis'],
            ['number' => 5, 'name' => 'Jumat'],
            ['number' => 6, 'name' => 'Sabtu'],
        ];

        foreach ($days as $day) {
            Day::firstOrCreate(['number' => $day['number']], $day);
        }
    }
}
