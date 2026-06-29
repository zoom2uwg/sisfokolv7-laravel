<?php

namespace Database\Seeders;

use App\Models\Hour;
use Illuminate\Database\Seeder;

class HourSeeder extends Seeder
{
    public function run(): void
    {
        $hours = [
            ['name' => 'Jam ke-1', 'start_time' => '07:00:00', 'end_time' => '07:45:00', 'order' => 1],
            ['name' => 'Jam ke-2', 'start_time' => '07:45:00', 'end_time' => '08:30:00', 'order' => 2],
            ['name' => 'Jam ke-3', 'start_time' => '08:30:00', 'end_time' => '09:15:00', 'order' => 3],
            ['name' => 'Jam ke-4', 'start_time' => '09:15:00', 'end_time' => '10:00:00', 'order' => 4],
            ['name' => 'Jam ke-5', 'start_time' => '10:15:00', 'end_time' => '11:00:00', 'order' => 5],
            ['name' => 'Jam ke-6', 'start_time' => '11:00:00', 'end_time' => '11:45:00', 'order' => 6],
            ['name' => 'Jam ke-7', 'start_time' => '11:45:00', 'end_time' => '12:30:00', 'order' => 7],
            ['name' => 'Jam ke-8', 'start_time' => '12:30:00', 'end_time' => '13:15:00', 'order' => 8],
            ['name' => 'Jam ke-9', 'start_time' => '13:15:00', 'end_time' => '14:00:00', 'order' => 9],
            ['name' => 'Jam ke-10', 'start_time' => '14:00:00', 'end_time' => '14:45:00', 'order' => 10],
        ];

        foreach ($hours as $hour) {
            Hour::firstOrCreate(['order' => $hour['order']], $hour);
        }
    }
}
