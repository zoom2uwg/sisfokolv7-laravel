<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\{RolePermissionSeeder, SuperAdminSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_auth(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_dashboard(): void
    {
        $this->seed([RolePermissionSeeder::class, SuperAdminSeeder::class]);
        $user = User::where('username', 'superadmin')->first();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee($user->nama);
    }

    public function test_dashboard_shows_impersonation_banner_when_active(): void
    {
        $this->seed([RolePermissionSeeder::class, SuperAdminSeeder::class]);
        config(['impersonate.enabled' => true]);
        $superadmin = User::where('username', 'superadmin')->first();
        $target = User::factory()->create();

        $this->actingAs($superadmin)->post("/impersonate/{$target->id}/start");
        $response = $this->get('/dashboard');
        $response->assertSee('Kembali ke akun saya');
    }
}
