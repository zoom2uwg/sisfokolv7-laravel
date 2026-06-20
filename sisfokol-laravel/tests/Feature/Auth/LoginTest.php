<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, SuperAdminSeeder::class]);
    }

    public function test_login_page_is_accessible_to_guest(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Login');
    }

    public function test_login_redirects_authenticated_user(): void
    {
        $user = User::where('username', 'superadmin')->first();
        $this->actingAs($user)->get('/login')->assertRedirect('/dashboard');
    }

    public function test_valid_credentials_log_in_superadmin(): void
    {
        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'SuperAdmin#2026',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs(User::where('username', 'superadmin')->first());
    }

    public function test_invalid_password_rejected(): void
    {
        $this->post('/login', ['username' => 'superadmin', 'password' => 'wrongpass']);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['aktif' => false]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_updates_last_login_at(): void
    {
        $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'SuperAdmin#2026',
        ]);
        $user = User::where('username', 'superadmin')->first();
        $this->assertNotNull($user->last_login_at);
    }

    public function test_throttle_blocks_after_5_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['username' => 'superadmin', 'password' => 'wrongpass']);
        }
        $response = $this->post('/login', ['username' => 'superadmin', 'password' => 'wrongpass']);
        $response->assertStatus(429); // Too Many Requests
    }
}
