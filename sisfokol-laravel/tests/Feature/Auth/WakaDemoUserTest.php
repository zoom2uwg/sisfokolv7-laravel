<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifikasi level-User: role Waka dapat di-assign ke User dan memberikan
 * permission yang tepat (manage bidang + view-only di luar).
 *
 * Catatan: pembuatan demo user via DemoSeeder diverifikasi manual di Task 4
 * (migrate:fresh --seed + login) karena DemoSeeder ada pre-existing issue
 * terkait tabel student_savings di env test (di luar scope Waka).
 */
class WakaDemoUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_waka_kurikulum_role_assignable_and_grants_correct_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $tenant = Tenant::create(['nama' => 'Sekolah T', 'npsn' => '55555551']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'tipe' => 'pegawai']);
        $user->assignRole('waka-kurikulum');

        $this->assertTrue($user->hasRole('waka-kurikulum'));
        // Manage bidang
        $this->assertTrue($user->can('kurikulum.manage'));
        $this->assertTrue($user->can('master.classroom.*'));
        // View-only di luar bidang
        $this->assertTrue($user->can('student.view'));
        $this->assertFalse($user->can('finance.*'));
        $this->assertFalse($user->can('student.*'));
    }

    public function test_waka_sarpras_role_assignable_and_grants_correct_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $tenant = Tenant::create(['nama' => 'Sekolah T', 'npsn' => '55555552']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'tipe' => 'pegawai']);
        $user->assignRole('waka-sarpras');

        $this->assertTrue($user->hasRole('waka-sarpras'));
        $this->assertTrue($user->can('inventory.*'));
        $this->assertTrue($user->can('master.school-profile.update'));
        // View-only
        $this->assertTrue($user->can('student.view'));
        $this->assertFalse($user->can('master.classroom.*'));
    }
}
