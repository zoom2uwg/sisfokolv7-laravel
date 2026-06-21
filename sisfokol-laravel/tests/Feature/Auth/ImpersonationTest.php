<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\{RolePermissionSeeder, SuperAdminSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, SuperAdminSeeder::class]);
        config(['impersonate.enabled' => true]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_impersonation_disabled_returns_404(): void
    {
        config(['impersonate.enabled' => false]);
        $superadmin = User::where('username', 'superadmin')->first();
        $target = User::factory()->create();

        $response = $this->actingAs($superadmin)
            ->post("/impersonate/{$target->id}/start");
        $response->assertStatus(404);
    }

    public function test_superadmin_can_impersonate_any_user(): void
    {
        $this->withoutExceptionHandling();
        $superadmin = User::where('username', 'superadmin')->first();
        $target = User::factory()->create(['nama' => 'Target User']);

        $response = $this->actingAs($superadmin)
            ->post("/impersonate/{$target->id}/start");

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('impersonated_by', $superadmin->id);
        $this->assertAuthenticatedAs($target);
    }

    public function test_stop_returns_to_original(): void
    {
        $superadmin = User::where('username', 'superadmin')->first();
        $target = User::factory()->create();

        $this->actingAs($superadmin)
            ->post("/impersonate/{$target->id}/start");
        $this->actingAs($target) // simulate impersonated session
            ->withSession(['impersonated_by' => $superadmin->id])
            ->post('/impersonate/stop');

        // After stop, original user should be restored
        $this->assertEquals($superadmin->id, auth()->id());
    }

    public function test_admin_sekolah_can_impersonate_tenant_user_only(): void
    {
        $t1 = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        $t2 = Tenant::create(['nama' => 'T2', 'npsn' => '22222222']);

        $adminT1 = User::factory()->create(['tenant_id' => $t1->id, 'tipe' => 'admin_sekolah']);
        $adminT1->assignRole('admin'); // 'admin' role has '*' permissions

        $targetInT1 = User::factory()->create(['tenant_id' => $t1->id, 'tipe' => 'guru']);
        $targetInT2 = User::factory()->create(['tenant_id' => $t2->id, 'tipe' => 'guru']);

        // OK in same tenant
        $this->actingAs($adminT1)
            ->post("/impersonate/{$targetInT1->id}/start")
            ->assertRedirect('/dashboard');

        // Forbidden cross-tenant
        $this->actingAs($adminT1)->post('/impersonate/stop');
        $response = $this->actingAs($adminT1)
            ->post("/impersonate/{$targetInT2->id}/start");
        $response->assertStatus(403);
    }

    public function test_blocked_action_while_impersonating_returns_403(): void
    {
        $superadmin = User::where('username', 'superadmin')->first();
        $target = User::factory()->create();

        $this->actingAs($superadmin)->post("/impersonate/{$target->id}/start");
        
        // POST to /users (sensitive) should be blocked (named as admin.users.store)
        // Set route to mock request target
        $response = $this->actingAs($target)
            ->withSession(['impersonated_by' => $superadmin->id])
            ->post('/admin/users', ['username' => 'test']);
        $response->assertStatus(403);
    }

    public function test_impersonation_creates_audit_logs(): void
    {
        $superadmin = User::where('username', 'superadmin')->first();
        $target = User::factory()->create();

        $this->actingAs($superadmin)->post("/impersonate/{$target->id}/start");
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superadmin->id,
            'event' => 'impersonate.start',
        ]);
    }
}
