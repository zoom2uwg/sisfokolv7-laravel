<?php

namespace Database\Seeders;

use App\Modules\Academic\Models\Guru;
use App\Modules\Academic\Models\Kelas;
use App\Modules\Academic\Models\Semester;
use App\Modules\Academic\Models\TahunAjaran;
use App\Modules\Academic\Models\Jadwal;
use App\Models\Subject;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get current tenant context
        $tenantCtx = app(TenantContext::class);
        $tenantId = $tenantCtx->id;

        if (!$tenantId) {
            // Fallback to first tenant
            $tenant = \App\Modules\Tenancy\Models\Tenant::first();
            if (!$tenant) {
                $this->command->error('No tenant found. Please run DemoSeeder first.');
                return;
            }
            $tenantId = $tenant->id;
            $tenantCtx->set($tenantId);
        }

        // 2. Resolve academic settings
        $tahunAjaran = TahunAjaran::where('aktif', true)->first();
        if (!$tahunAjaran) {
            $tahunAjaran = TahunAjaran::first();
        }
        if (!$tahunAjaran) {
            $this->command->error('No academic year found. Please run DemoSeeder first.');
            return;
        }

        $semester = Semester::where('tahun_ajaran_id', $tahunAjaran->id)->where('aktif', true)->first();
        if (!$semester) {
            $semester = Semester::where('tahun_ajaran_id', $tahunAjaran->id)->first();
        }
        if (!$semester) {
            $this->command->error('No semester found. Please run DemoSeeder first.');
            return;
        }

        // 3. Resolve classrooms and teachers
        $classes = Kelas::all();
        $gurus = Guru::where('aktif', true)->get();

        if ($classes->isEmpty() || $gurus->isEmpty()) {
            $this->command->error('Classes or Gurus empty. Please run DemoSeeder first.');
            return;
        }

        // 4. Ensure multiple subjects exist
        $subjectsData = [
            ['code' => 'MTK', 'name' => 'Matematika'],
            ['code' => 'IND', 'name' => 'Bahasa Indonesia'],
            ['code' => 'ING', 'name' => 'Bahasa Inggris'],
            ['code' => 'FIS', 'name' => 'Fisika'],
            ['code' => 'KIM', 'name' => 'Kimia'],
            ['code' => 'BIO', 'name' => 'Biologi'],
            ['code' => 'SEJ', 'name' => 'Sejarah'],
            ['code' => 'AGM', 'name' => 'Pendidikan Agama'],
            ['code' => 'PJK', 'name' => 'Pendidikan Jasmani'],
        ];

        $subjects = [];
        foreach ($subjectsData as $s) {
            $subject = Subject::firstOrCreate(
                ['code' => $s['code'], 'academic_year_id' => $tahunAjaran->id],
                [
                    'name' => $s['name'],
                    'is_exam' => true,
                    'phase' => 'E',
                    'tenant_id' => $tenantId,
                ]
            );
            $subjects[] = \App\Modules\Academic\Models\Mapel::find($subject->id);
        }

        // Time slot definitions mapping jam_ke to times
        $timeSlots = [
            1 => ['start' => '07:30', 'end' => '08:15'],
            2 => ['start' => '08:15', 'end' => '09:00'],
            3 => ['start' => '09:15', 'end' => '10:00'],
            4 => ['start' => '10:00', 'end' => '10:45'],
            5 => ['start' => '10:45', 'end' => '11:30'],
        ];

        // Track busy teachers to avoid double-booking
        // busy_teachers[hari][jam_ke] = [guru_id, guru_id, ...]
        $busyTeachers = [];

        // Track created schedules
        $count = 0;

        foreach ($classes as $kelas) {
            // Seed schedule for days Monday to Friday (1 to 5)
            for ($hari = 1; $hari <= 5; $hari++) {
                for ($jamKe = 1; $jamKe <= 4; $jamKe++) { // 4 slots per day
                    $slotTime = $timeSlots[$jamKe];

                    // Find a teacher who is not busy in this slot
                    $availableGurus = $gurus->filter(function ($guru) use ($hari, $jamKe, $busyTeachers) {
                        return !isset($busyTeachers[$hari][$jamKe]) || !in_array($guru->id, $busyTeachers[$hari][$jamKe]);
                    });

                    if ($availableGurus->isEmpty()) {
                        // Skip if all teachers are busy in this slot
                        continue;
                    }

                    $guru = $availableGurus->random();
                    $mapel = collect($subjects)->random();

                    // Create schedule slot
                    Jadwal::create([
                        'tenant_id' => $tenantId,
                        'tahun_ajaran_id' => $tahunAjaran->id,
                        'semester_id' => $semester->id,
                        'kelas_id' => $kelas->id,
                        'mapel_id' => $mapel->id,
                        'guru_id' => $guru->id,
                        'hari' => $hari,
                        'jam_ke' => $jamKe,
                        'jam_mulai' => $slotTime['start'],
                        'jam_selesai' => $slotTime['end'],
                        'ruang' => 'Ruang ' . rand(101, 108),
                    ]);

                    // Mark teacher as busy
                    $busyTeachers[$hari][$jamKe][] = $guru->id;
                    $count++;
                }
            }
        }

        $this->command->info("✅ JadwalSeeder completed: {$count} schedule slots created.");
    }
}
