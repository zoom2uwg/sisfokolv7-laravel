<?php

namespace Tests\Feature\Rbac;

use App\Models\User;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\{RolePermissionSeeder, MenuSeeder, FieldSeeder, SuperAdminSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_rbac_builder(): void
    {
        $this->seed([RolePermissionSeeder::class, MenuSeeder::class, FieldSeeder::class]);
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('student');

        $this->actingAs($user)->get('/admin/rbac')->assertStatus(403);
    }

    public function test_admin_sekolah_can_access_rbac_index(): void
    {
        $this->seed([RolePermissionSeeder::class, MenuSeeder::class, FieldSeeder::class, SuperAdminSeeder::class]);
        
        // Dynamically create the rbac.manage permission and assign it to admin
        \Spatie\Permission\Models\Permission::findOrCreate('rbac.manage', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->givePermissionTo('rbac.manage');

        $response = $this->actingAs($admin)->get('/admin/rbac');
        $response->assertStatus(200);
        $response->assertSee('RBAC Builder');
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $this->seed([RolePermissionSeeder::class, MenuSeeder::class, FieldSeeder::class, SuperAdminSeeder::class]);
        \Spatie\Permission\Models\Permission::findOrCreate('rbac.manage', 'web');
        
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->givePermissionTo('rbac.manage');

        $roleId = \Spatie\Permission\Models\Role::where('name', 'teacher')->first()->id;
        $permId = \Spatie\Permission\Models\Permission::where('name', 'presence.view')->first()->id;

        $response = $this->actingAs($admin)
            ->post("/admin/rbac/role/{$roleId}/permissions", [
                'permissions' => [$permId],
            ]);

        $response->assertStatus(200);
        $role = \Spatie\Permission\Models\Role::find($roleId);
        $this->assertTrue($role->permissions->contains($permId));
    }

    public function test_rbac_change_blocked_while_impersonating(): void
    {
        $this->seed([RolePermissionSeeder::class, MenuSeeder::class, FieldSeeder::class, SuperAdminSeeder::class]);
        \Spatie\Permission\Models\Permission::findOrCreate('rbac.manage', 'web');
        config(['impersonate.enabled' => true]);

        $super = User::where('username', 'superadmin')->first();
        $target = User::factory()->create();
        $this->actingAs($super)->post("/impersonate/{$target->id}/start");

        $response = $this->post('/admin/rbac/role/1/permissions', ['permissions' => []]);
        $response->assertStatus(403);
    }
}
