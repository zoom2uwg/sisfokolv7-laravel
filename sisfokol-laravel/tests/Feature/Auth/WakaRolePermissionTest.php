<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WakaRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_three_waka_roles_exist_after_seeding(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertNotNull(Role::findByName('waka-kurikulum', 'web'));
        $this->assertNotNull(Role::findByName('waka-kesiswaan', 'web'));
        $this->assertNotNull(Role::findByName('waka-sarpras', 'web'));
    }

    public function test_waka_kurikulum_can_manage_kurikulum_and_academic(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-kurikulum', 'web');

        $this->assertTrue($role->hasPermissionTo('kurikulum.manage'));
        $this->assertTrue($role->hasPermissionTo('master.classroom.*'));
        $this->assertTrue($role->hasPermissionTo('master.subject.*'));
        $this->assertTrue($role->hasPermissionTo('academic.schedule.*'));
        $this->assertTrue($role->hasPermissionTo('academic.curriculum.*'));
    }

    public function test_waka_kurikulum_is_view_only_outside_bidang(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-kurikulum', 'web');

        // View siswa (menu + policy)
        $this->assertTrue($role->hasPermissionTo('student.view'));
        $this->assertTrue($role->hasPermissionTo('siswa.view'));
        // View keuangan
        $this->assertTrue($role->hasPermissionTo('finance.student-bill.view'));
        $this->assertTrue($role->hasPermissionTo('tagihan.view'));
        // Tidak manage finance
        $this->assertFalse($role->hasPermissionTo('finance.*'));
    }

    public function test_waka_kesiswaan_can_manage_siswa_and_discipline(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-kesiswaan', 'web');

        $this->assertTrue($role->hasPermissionTo('student.*'));
        $this->assertTrue($role->hasPermissionTo('violation.*'));
        $this->assertTrue($role->hasPermissionTo('counseling.*'));
        $this->assertTrue($role->hasPermissionTo('achievement.*'));
        $this->assertTrue($role->hasPermissionTo('permit.*'));
    }

    public function test_waka_kesiswaan_cannot_manage_academic_or_finance(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-kesiswaan', 'web');

        $this->assertFalse($role->hasPermissionTo('master.classroom.*'));
        $this->assertFalse($role->hasPermissionTo('finance.*'));
        $this->assertTrue($role->hasPermissionTo('kelas.view'));
    }

    public function test_waka_sarpras_can_manage_inventory_and_room(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-sarpras', 'web');

        $this->assertTrue($role->hasPermissionTo('inventory.*'));
        $this->assertTrue($role->hasPermissionTo('master.room.*'));
        $this->assertTrue($role->hasPermissionTo('master.school-profile.update'));
    }

    public function test_waka_sarpras_is_view_only_outside_bidang(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-sarpras', 'web');

        $this->assertFalse($role->hasPermissionTo('student.*'));
        $this->assertFalse($role->hasPermissionTo('master.classroom.*'));
        $this->assertTrue($role->hasPermissionTo('student.view'));
        $this->assertTrue($role->hasPermissionTo('siswa.view'));
    }
}
