<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Master
            'master.school-profile.view',
            'master.school-profile.update',
            'master.academic-year.*',
            'master.classroom.*',
            'master.room.*',
            'master.subject.*',
            'master.subject-type.*',
            'master.extracurricular.*',
            'master.violation-type.*',
            'master.violation-point.*',
            'master.achievement-type.*',
            'master.counseling-type.*',

            // User Management
            'user.*',
            'user.view',
            'employee.*',
            'employee.view',
            'student.*',
            'student.view',

            // Academic
            'academic.schedule.*',
            'academic.schedule.view',
            'academic.teacher-agenda.*',
            'academic.curriculum.*',

            // Presence & Absence
            'presence.*',
            'presence.view',
            'absence.*',
            'absence.view',
            'permit.*',

            // Discipline
            'violation.*',
            'violation.view',
            'counseling.*',
            'achievement.*',

            // Finance
            'finance.*',
            'finance.payment-item.*',
            'finance.student-bill.*',
            'finance.student-bill.view',
            'finance.student-payment.*',
            'finance.student-payment.view',
            'finance.student-saving.*',
            'finance.report.*',


            // Inventory
            'inventory.*',

            // Reports
            'report.*',

            // Settings
            'setting.*',
            'plugin.activate',

            // Indonesian menu and functional permissions
            'dashboard.view',
            'tenant.view',
            'user.manage',
            'rbac.manage',
            'audit.view',
            'siswa.view',
            'guru.view',
            'kelas.view',
            'mapel.view',
            'jadwal.view',
            'tagihan.view',
            'pembayaran.view',
            'tabungan.view',
            'presensi.view',
            'absensi.view',
            'raport.view',

            // Waka — kurikulum plugin permissions (idempotent dengan app/Plugins/Kurikulum/permissions.php)
            'kurikulum.view',
            'kurikulum.manage',

            // Waka — view-only tabungan (fix gap TabunganPolicy, lihat Task 3)
            'finance.student-saving.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'admin' => ['*'],
            'principal' => [
                'master.school-profile.view',
                'report.*',
                'user.view',
                'employee.view',
                'student.view',
                'dashboard.view',
                'siswa.view',
                'guru.view',
                'kelas.view',
                'mapel.view',
                'jadwal.view',
                'tagihan.view',
                'pembayaran.view',
                'tabungan.view',
                'presensi.view',
                'absensi.view',
                'raport.view',
            ],
            'teacher' => [
                'academic.schedule.view',
                'academic.teacher-agenda.*',
                'academic.curriculum.*',
                'presence.view',
                'absence.view',
                'dashboard.view',
                'siswa.view',
                'guru.view',
                'kelas.view',
                'mapel.view',
                'jadwal.view',
                'presensi.view',
                'absensi.view',
                'raport.view',
            ],
            'student' => [
                'academic.schedule.view',
                'presence.view',
                'absence.view',
                'finance.student-bill.view',
                'finance.student-payment.view',
                'dashboard.view',
                'jadwal.view',
                'presensi.view',
                'absensi.view',
            ],
            'homeroom-teacher' => [
                'academic.schedule.view',
                'academic.curriculum.*',
                'student.view',
                'absence.*',
                'violation.*',
                'achievement.*',
                'report.*',
                'dashboard.view',
                'siswa.view',
                'guru.view',
                'kelas.view',
                'mapel.view',
                'jadwal.view',
                'presensi.view',
                'absensi.view',
                'raport.view',
            ],
            'finance' => [
                'finance.*',
                'student.view',
                'report.*',
                'dashboard.view',
                'siswa.view',
                'tagihan.view',
                'pembayaran.view',
                'tabungan.view',
            ],
            'counselor' => [
                'violation.*',
                'counseling.*',
                'achievement.*',
                'student.view',
                'absence.*',
                'permit.*',
                'dashboard.view',
                'siswa.view',
                'presensi.view',
                'absensi.view',
            ],
            'picket-officer' => [
                'presence.*',
                'absence.*',
                'permit.*',
                'violation.view',
                'dashboard.view',
                'presensi.view',
                'absensi.view',
            ],
            'inventory' => [
                'inventory.*',
            ],
            'waka-kurikulum' => [
                // Manage: Kurikulum & Akademik
                'kurikulum.manage', 'kurikulum.view',
                'master.subject.*', 'master.subject-type.*', 'mapel.view',
                'master.classroom.*', 'kelas.view',
                'master.academic-year.*',
                'master.room.*',
                'master.extracurricular.*',
                'academic.schedule.*', 'jadwal.view',
                'academic.curriculum.*', 'academic.teacher-agenda.*',
                // View: bidang lain
                'student.view', 'siswa.view',
                'employee.view', 'guru.view',
                'presence.view', 'presensi.view',
                'absence.view', 'absensi.view',
                'finance.student-bill.view', 'tagihan.view',
                'finance.student-payment.view', 'pembayaran.view',
                'finance.student-saving.view', 'tabungan.view',
                'raport.view',
                'dashboard.view', 'report.*', 'master.school-profile.view',
            ],
            'waka-kesiswaan' => [
                // Manage: Kesiswaan, Organisasi
                'student.*', 'siswa.view',
                'violation.*', 'master.violation-type.*', 'master.violation-point.*',
                'counseling.*', 'master.counseling-type.*',
                'achievement.*', 'master.achievement-type.*',
                'permit.*',
                'absence.*', 'absensi.view',
                'master.extracurricular.*',
                // View: bidang lain
                'kurikulum.view',
                'employee.view', 'guru.view', 'kelas.view', 'mapel.view',
                'academic.schedule.view', 'jadwal.view',
                'presence.view', 'presensi.view',
                'finance.student-bill.view', 'tagihan.view',
                'finance.student-payment.view', 'pembayaran.view',
                'finance.student-saving.view', 'tabungan.view',
                'raport.view',
                'dashboard.view', 'report.*', 'master.school-profile.view',
            ],
            'waka-sarpras' => [
                // Manage: Sarana Prasarana
                'inventory.*',
                'master.room.*',
                'master.school-profile.update', 'master.school-profile.view',
                // View: bidang lain
                'student.view', 'siswa.view', 'employee.view', 'guru.view',
                'kelas.view', 'mapel.view',
                'academic.schedule.view', 'jadwal.view',
                'presence.view', 'presensi.view', 'absence.view', 'absensi.view',
                'finance.student-bill.view', 'tagihan.view',
                'finance.student-payment.view', 'pembayaran.view',
                'finance.student-saving.view', 'tabungan.view',
                'raport.view', 'kurikulum.view',
                'dashboard.view', 'report.*',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');

            if (in_array('*', $rolePermissions)) {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($rolePermissions);
            }
        }
    }
}
