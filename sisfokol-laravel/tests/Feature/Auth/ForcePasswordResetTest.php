<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\{RolePermissionSeeder, SuperAdminSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForcePasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_must_reset_is_redirected_to_change_password(): void
    {
        $this->seed([RolePermissionSeeder::class, SuperAdminSeeder::class]);
        $user = User::factory()->create([
            'tenant_id' => null,
            'tipe' => 'admin_sekolah',
            'must_reset_password' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/password/change');
    }

    public function test_change_password_clears_flag(): void
    {
        $this->seed([RolePermissionSeeder::class, SuperAdminSeeder::class]);
        $user = User::factory()->create([
            'tenant_id' => null,
            'must_reset_password' => true,
        ]);

        $this->actingAs($user)
            ->post('/password/change', [
                'current_password' => 'password',
                'password' => 'NewSecure#2026',
                'password_confirmation' => 'NewSecure#2026',
            ])
            ->assertRedirect('/dashboard');

        $this->assertFalse($user->fresh()->must_reset_password);
    }

    public function test_change_password_route_not_blocked_by_middleware(): void
    {
        $this->seed([RolePermissionSeeder::class, SuperAdminSeeder::class]);
        $user = User::factory()->create(['must_reset_password' => true]);
        $this->actingAs($user)->get('/password/change')->assertStatus(200);
    }
}
