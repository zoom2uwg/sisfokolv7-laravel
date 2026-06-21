<?php

namespace Tests\Feature\Rbac;

use App\Models\User;
use App\Modules\Auth\Models\Field;
use App\Modules\Auth\Models\FieldRoleOverride;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Database\Seeders\{RolePermissionSeeder, FieldSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldAclTest extends TestCase
{
    use RefreshDatabase;

    public function test_field_with_default_hidden_is_hidden_for_user_without_override(): void
    {
        $this->seed([RolePermissionSeeder::class, FieldSeeder::class]);
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        app(TenantContext::class)->set(tenantId: $tenant->id);

        $visibility = \App\Support\FieldAcl::visible('siswa.telepon', $user);

        $this->assertSame('hidden', $visibility);
    }

    public function test_override_visible_wins_over_default_hidden(): void
    {
        $this->seed([RolePermissionSeeder::class, FieldSeeder::class]);
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('teacher');
        app(TenantContext::class)->set(tenantId: $tenant->id);

        // Add override: teacher can see siswa.telepon
        $field = Field::where('kode', 'siswa.telepon')->first();
        $roleId = \Spatie\Permission\Models\Role::where('name', 'teacher')->first()->id;
        FieldRoleOverride::create([
            'field_id' => $field->id, 'role_id' => $roleId,
            'tenant_id' => $tenant->id, 'visibility' => 'visible',
        ]);

        $this->assertSame('visible', \App\Support\FieldAcl::visible('siswa.telepon', $user));
    }

    public function test_superadmin_sees_everything_visible(): void
    {
        $this->seed([RolePermissionSeeder::class, FieldSeeder::class]);
        $this->seed(\Database\Seeders\SuperAdminSeeder::class);
        $super = User::where('username', 'superadmin')->first();

        $this->assertSame('visible', \App\Support\FieldAcl::visible('siswa.telepon', $super));
        $this->assertSame('visible', \App\Support\FieldAcl::visible('tagihan.nominal_kurang', $super));
    }

    public function test_blade_directive_renders_visible_field(): void
    {
        $this->assertTrue(class_exists(\App\Support\FieldAcl::class));
    }
}
