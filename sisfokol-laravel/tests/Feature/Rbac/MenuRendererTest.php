<?php

namespace Tests\Feature\Rbac;

use App\Models\User;
use App\Modules\Auth\Models\Menu;
use App\Modules\Auth\Models\MenuRoleOverride;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Database\Seeders\{RolePermissionSeeder, MenuSeeder, SuperAdminSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuRendererTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_sees_all_active_menus(): void
    {
        $this->seed([RolePermissionSeeder::class, MenuSeeder::class, SuperAdminSeeder::class]);
        $super = User::where('username', 'superadmin')->first();

        $items = \App\Support\MenuRenderer::forUser($super);

        $codes = collect($items)->pluck('kode');
        $this->assertContains('dashboard', $codes);
        $this->assertContains('tenancy.tenants', $codes);
        $this->assertContains('auth.rbac', $codes);
    }

    public function test_menu_hidden_by_role_override(): void
    {
        $this->seed([RolePermissionSeeder::class, MenuSeeder::class]);
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('teacher');
        app(TenantContext::class)->set(tenantId: $tenant->id);

        // Override: hide 'finance.tagihan' from teacher
        $menu = Menu::where('kode', 'finance.tagihan')->first();
        $roleId = \Spatie\Permission\Models\Role::where('name', 'teacher')->first()->id;
        MenuRoleOverride::create([
            'menu_id' => $menu->id,
            'role_id' => $roleId,
            'tenant_id' => $tenant->id,
            'visible' => 'hide'
        ]);

        $items = \App\Support\MenuRenderer::forUser($user);
        $codes = collect($items)->pluck('kode');
        $this->assertNotContains('finance.tagihan', $codes);
    }

    public function test_menu_filtered_by_permission_required(): void
    {
        $this->seed([RolePermissionSeeder::class, MenuSeeder::class]);
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('student'); // student only has dashboard.view + raport.view

        $items = \App\Support\MenuRenderer::forUser($user);
        $codes = collect($items)->pluck('kode');
        $this->assertContains('dashboard', $codes);
        $this->assertNotContains('tenancy.tenants', $codes);
        $this->assertNotContains('finance.tagihan', $codes);
    }
}
