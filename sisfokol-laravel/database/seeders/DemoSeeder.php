<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceTime;
use App\Models\Permit;
use App\Models\User;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Academic\Models\Guru;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Buat Tenant Demo ───────────────────────────────────────────
        $tenant = Tenant::create([
            'nama'     => 'SMA Demo Sisfokol',
            'npsn'     => '20000001',
            'jenjang'  => 'SMA',
            'alamat'   => 'Jl. Pendidikan No. 1, Kota Demo',
            'telepon'  => '021-1234567',
            'email'    => 'info@smademo.sch.id',
            'aktif'    => true,
        ]);

        app(TenantContext::class)->set($tenant->id);

        // ─── 1b. Academic Year, Tahun Ajaran, Semester ─────────────────────
        $academicYear = \App\Models\AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        // Since they use the same table (tahun_ajaran), retrieve the mapped model instance.
        $tahunAjaran = \App\Modules\Academic\Models\TahunAjaran::find($academicYear->id);

        $semester = \App\Modules\Academic\Models\Semester::create([
            'tenant_id' => $tenant->id,
            'tahun_ajaran_id' => $tahunAjaran->id,
            'nama' => 1, // Semester 1 / Ganjil
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'aktif' => true,
        ]);

        // ─── 1c. Subject & Room ────────────────────────────────────────────
        $subject = \App\Models\Subject::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Matematika',
            'code' => 'MTK',
            'is_exam' => true,
            'phase' => 'E',
        ]);

        $room = \App\Models\Room::create([
            'name' => 'Ruang 101',
            'code' => 'R101',
            'capacity' => 40,
        ]);

        // ─── 1d. Classroom & Kelas ─────────────────────────────────────────
        $classroom = \App\Models\Classroom::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Kelas X-A',
            'level' => '10',
            'major' => 'Umum',
            'capacity' => 36,
        ]);

        // [2026-06-25 | AI-Agent] Avoid duplicate primary key insert, retrieve and update the mapped model instance.
        // $kelas = \App\Modules\Academic\Models\Kelas::create([
        //     'id' => $classroom->id,
        //     'tenant_id' => $tenant->id,
        //     'nama' => 'Kelas X-A',
        //     'tingkat' => 10,
        //     'kapasitas' => 36,
        // ]);
        $kelas = \App\Modules\Academic\Models\Kelas::find($classroom->id);
        $kelas->update([
            'tingkat' => 10,
        ]);

        // ─── 2. Users per Role ─────────────────────────────────────────────
        $users = [
            [
                'username' => 'admin.sekolah',
                'nama'     => 'Admin Sekolah Demo',
                'email'    => 'admin@smademo.sch.id',
                'tipe'     => 'admin_sekolah',
                'role'     => 'admin',
            ],
            [
                'username' => 'piket.demo',
                'nama'     => 'Guru Piket Demo',
                'email'    => 'piket@smademo.sch.id',
                'tipe'     => 'pegawai',
                'role'     => 'picket-officer',
            ],
            [
                'username' => 'bk.demo',
                'nama'     => 'Guru BK Demo',
                'email'    => 'bk@smademo.sch.id',
                'tipe'     => 'pegawai',
                'role'     => 'counselor',
            ],
            [
                'username' => 'guru.demo',
                'nama'     => 'Guru Mapel Demo',
                'email'    => 'guru@smademo.sch.id',
                'tipe'     => 'pegawai',
                'role'     => 'teacher',
            ],
            [
                'username' => 'walikelas.demo',
                'nama'     => 'Wali Kelas Demo',
                'email'    => 'walikelas@smademo.sch.id',
                'tipe'     => 'pegawai',
                'role'     => 'homeroom-teacher',
            ],
        ];

        $createdUsers = [];
        $createdEmployees = [];
        $createdGurus = [];

        foreach ($users as $u) {
            $user = User::create([
                'tenant_id' => $tenant->id,
                'username'  => $u['username'],
                'nama'      => $u['nama'],
                'email'     => $u['email'],
                'tipe'      => $u['tipe'],
                'password'  => Hash::make('demo1234'),
                'aktif'     => true,
            ]);
            $user->assignRole($u['role']);
            $createdUsers[$u['role']] = $user;

            // Create matching Employee & Guru
            $employee = \App\Models\Employee::create([
                'user_id' => $user->id,
                'code' => 'EMP-' . strtoupper($u['username']),
                'name' => $u['nama'],
                'gender' => 'L',
                'position' => $u['role'],
                'status' => 'aktif',
            ]);
            $createdEmployees[$u['role']] = $employee;

            $guru = \App\Modules\Academic\Models\Guru::create([
                'tenant_id' => $tenant->id,
                'nip' => 'NIP-' . strtoupper($u['username']),
                'nama' => $u['nama'],
                'jenis_kelamin' => 'L',
                'jabatan' => $u['role'],
                'aktif' => true,
            ]);
            $createdGurus[$u['role']] = $guru;

            // Associate user userable
            $user->update([
                'userable_type' => \App\Models\Employee::class,
                'userable_id' => $employee->id,
            ]);
        }

        // Link Wali Kelas
        $kelas->update(['wali_kelas_id' => $createdGurus['homeroom-teacher']->id]);
        $classroom->update(['homeroom_teacher_id' => $createdEmployees['homeroom-teacher']->id]);

        $day = \App\Models\Day::where('number', 1)->first() ?? \App\Models\Day::first();
        $timeSlot = \App\Models\TimeSlot::first();

        // ─── 2b. Teaching Schedule ─────────────────────────────────────────
        $schedule = \App\Models\Schedule::create([
            'academic_year_id' => $academicYear->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'employee_id' => $createdEmployees['teacher']->id,
            'room_id' => $room->id,
            'day_id' => $day?->id ?? 1, // Senin
            'time_slot_id' => $timeSlot?->id ?? 1,
            'description' => 'Matematika Kelas X-A',
        ]);

        // ─── 3. Buat 20 Siswa Demo ─────────────────────────────────────────
        $siswaData = [
            ['nis' => '2024001', 'nama' => 'Andi Pratama',        'jk' => 'L'],
            ['nis' => '2024002', 'nama' => 'Budi Santoso',        'jk' => 'L'],
            ['nis' => '2024003', 'nama' => 'Citra Dewi',          'jk' => 'P'],
            ['nis' => '2024004', 'nama' => 'Dian Permatasari',    'jk' => 'P'],
            ['nis' => '2024005', 'nama' => 'Eko Wahyudi',         'jk' => 'L'],
            ['nis' => '2024006', 'nama' => 'Fani Rahayu',         'jk' => 'P'],
            ['nis' => '2024007', 'nama' => 'Gilang Ramadhan',     'jk' => 'L'],
            ['nis' => '2024008', 'nama' => 'Hana Safitri',        'jk' => 'P'],
            ['nis' => '2024009', 'nama' => 'Irfan Maulana',       'jk' => 'L'],
            ['nis' => '2024010', 'nama' => 'Jihan Aulia',         'jk' => 'P'],
            ['nis' => '2024011', 'nama' => 'Kevin Alamsyah',      'jk' => 'L'],
            ['nis' => '2024012', 'nama' => 'Lestari Wulandari',   'jk' => 'P'],
            ['nis' => '2024013', 'nama' => 'Muhammad Rizki',      'jk' => 'L'],
            ['nis' => '2024014', 'nama' => 'Nadia Kusuma',        'jk' => 'P'],
            ['nis' => '2024015', 'nama' => 'Oscar Firmansyah',    'jk' => 'L'],
            ['nis' => '2024016', 'nama' => 'Putri Anggraini',     'jk' => 'P'],
            ['nis' => '2024017', 'nama' => 'Raka Aditya',         'jk' => 'L'],
            ['nis' => '2024018', 'nama' => 'Siti Aisyah',         'jk' => 'P'],
            ['nis' => '2024019', 'nama' => 'Taufik Hidayat',      'jk' => 'L'],
            ['nis' => '2024020', 'nama' => 'Ulfa Novianti',       'jk' => 'P'],
        ];

        $siswaDibuat = [];
        foreach ($siswaData as $s) {
            $siswa = Siswa::create([
                'tenant_id'    => $tenant->id,
                'nis'          => $s['nis'],
                'nama'         => $s['nama'],
                'jenis_kelamin'=> $s['jk'],
                'tanggal_lahir'=> '2007-01-15',
                'agama'        => 'Islam',
                'status'       => 'aktif',
            ]);

            // [2026-06-25 | AI-Agent] Avoid duplicate primary key insert, retrieve and update the mapped model instance.
            // $student = \App\Models\Student::create([
            //     'id' => $siswa->id,
            //     'academic_year_id' => $academicYear->id,
            //     'classroom_id' => $classroom->id,
            //     'nis' => $s['nis'],
            //     'name' => $s['nama'],
            //     'gender' => $s['jk'],
            //     'is_active' => true,
            // ]);
            $student = \App\Models\Student::find($siswa->id);
            $student->update([
                'academic_year_id' => $academicYear->id,
                'classroom_id' => $classroom->id,
                'is_active' => true,
            ]);

            // Assign Student to Classroom in Academic Module
            \App\Modules\Academic\Models\KelasSiswa::create([
                'kelas_id' => $kelas->id,
                'siswa_id' => $siswa->id,
                'tahun_ajaran_id' => $tahunAjaran->id,
                'tenant_id' => $tenant->id,
                'no_urut' => count($siswaDibuat) + 1,
            ]);

            // Buat User untuk setiap siswa
            $userSiswa = User::create([
                'tenant_id'     => $tenant->id,
                'username'      => 'siswa.' . $s['nis'],
                'nama'          => $s['nama'],
                'email'         => 'siswa' . $s['nis'] . '@smademo.sch.id',
                'tipe'          => 'siswa',
                'password'      => Hash::make('demo1234'),
                'aktif'         => true,
                'userable_type' => \App\Modules\Academic\Models\Siswa::class,
                'userable_id'   => $siswa->id,
            ]);
            $userSiswa->assignRole('student');

            $siswaDibuat[] = ['siswa' => $siswa, 'user' => $userSiswa, 'student' => $student];
        }

        // ─── 4. Subject Descriptions for Rapor ───────────────────────────
        $predicates = ['A', 'B', 'C', 'D'];
        $descriptions = [
            'A' => 'Sangat menguasai seluruh materi pembelajaran matematika dengan sangat baik, mampu memecahkan masalah kompleks secara mandiri.',
            'B' => 'Menguasai kompetensi dasar matematika dengan baik dan aktif berpartisipasi dalam pemecahan masalah kelas.',
            'C' => 'Cukup menguasai kompetensi dasar matematika, memerlukan pendampingan pada latihan mandiri yang lebih kompleks.',
            'D' => 'Kurang memenuhi batas kompetensi minimal matematika, memerlukan bimbingan intensif dan latihan tambahan.',
        ];

        foreach ($predicates as $pred) {
            \App\Models\SubjectDescription::create([
                'tenant_id' => $tenant->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $academicYear->id,
                'category' => $pred,
                'description' => $descriptions[$pred],
            ]);
        }

        // ─── 5. Buat Data Presensi 7 Hari Terakhir ────────────────────────
        $picketUser = $createdUsers['picket-officer'];

        for ($day = 6; $day >= 0; $day--) {
            $date = Carbon::today()->subDays($day);

            // Skip weekend
            if ($date->isWeekend()) continue;

            foreach ($siswaDibuat as $idx => $sd) {
                $siswa   = $sd['siswa'];
                $userSis = $sd['user'];

                // 18 dari 20 siswa hadir, 2 alpa
                if ($idx >= 18) continue;

                // 15 tepat waktu, 3 terlambat
                $status = $idx < 15 ? 'present' : 'late';
                $timeIn = $status === 'present'
                    ? Carbon::parse($date->format('Y-m-d') . ' 06:' . str_pad(rand(30, 59), 2, '0', STR_PAD_LEFT))
                    : Carbon::parse($date->format('Y-m-d') . ' 07:' . str_pad(rand(35, 59), 2, '0', STR_PAD_LEFT));

                Attendance::create([
                    'tenant_id'      => $tenant->id,
                    'user_id'        => $userSis->id,
                    'attendable_type'=> Siswa::class,
                    'attendable_id'  => $siswa->id,
                    'date'           => $date->toDateString(),
                    'time'           => $timeIn->format('H:i:s'),
                    'type'           => 'in',
                    'source'         => 'qr',
                    'status'         => $status,
                    'created_by'     => $picketUser->id,
                ]);
            }
        }

        // ─── 6. Buat Data Izin (3 pending, 2 approved, 1 rejected) ────────
        $izinData = [
            ['siswa' => $siswaDibuat[0], 'type' => 'sick',       'status' => 'pending',  'reason' => 'Demam tinggi dan flu berat sejak kemarin malam'],
            ['siswa' => $siswaDibuat[1], 'type' => 'sick',       'status' => 'pending',  'reason' => 'Sakit kepala dan mual, tidak bisa berangkat sekolah'],
            ['siswa' => $siswaDibuat[2], 'type' => 'permission', 'status' => 'pending',  'reason' => 'Ada acara keluarga penting yang tidak bisa ditinggalkan'],
            ['siswa' => $siswaDibuat[3], 'type' => 'sick',       'status' => 'approved', 'reason' => 'Demam dan radang tenggorokan (ada surat dokter)'],
            ['siswa' => $siswaDibuat[4], 'type' => 'permission', 'status' => 'approved', 'reason' => 'Mengikuti lomba sains tingkat provinsi'],
            ['siswa' => $siswaDibuat[5], 'type' => 'other',      'status' => 'rejected', 'reason' => 'Alasan tidak jelas'],
        ];

        $counselorUser = $createdUsers['counselor'];

        foreach ($izinData as $iz) {
            $permit = Permit::create([
                'tenant_id'      => $tenant->id,
                'user_id'        => $picketUser->id,
                'permitable_type'=> Siswa::class,
                'permitable_id'  => $iz['siswa']['siswa']->id,
                'date'           => Carbon::today()->subDays(rand(0, 3))->toDateString(),
                'type'           => $iz['type'],
                'reason'         => $iz['reason'],
                'status'         => $iz['status'],
                'approved_by'    => in_array($iz['status'], ['approved', 'rejected']) ? $counselorUser->id : null,
                'approved_at'    => in_array($iz['status'], ['approved', 'rejected']) ? now() : null,
                'note'           => $iz['status'] === 'rejected' ? 'Alasan tidak dilengkapi surat resmi' : null,
                'created_by'     => $picketUser->id,
            ]);
        }

        app(TenantContext::class)->clear();

        $this->command->info('✅ DemoSeeder selesai!');
        $this->command->table(
            ['Role', 'Username', 'Password'],
            [
                ['SuperAdmin',      'superadmin',    'SuperAdmin#2026'],
                ['Admin Sekolah',   'admin',         'password'],
                ['Admin (Tenant)',  'admin.sekolah', 'demo1234'],
                ['Guru Piket',      'piket.demo',    'demo1234'],
                ['Guru BK',         'bk.demo',       'demo1234'],
                ['Guru Mapel',      'guru.demo',     'demo1234'],
                ['Wali Kelas',      'walikelas.demo','demo1234'],
                ['Siswa (contoh)',  'siswa.2024001', 'demo1234'],
            ]
        );
    }
}
