<?php

namespace Tests\Feature\Plugin;

use App\Models\User;
use App\Modules\Tenancy\Models\Tenant;
use App\Plugins\Infrastructure\Models\Plugin;
use App\Plugins\Infrastructure\Models\TenantPlugin;
use App\Plugins\Kurikulum\Models\Kurikulum;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Verifies KurikulumController CRUD endpoints work end-to-end after the
 * CRUDLFIX refactor (DEV_DOCS-064). Covers tenant isolation + authorization.
 */
class KurikulumCrudTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class]);

        // Seed kurikulum permissions (normally seeded on plugin activation)
        foreach (['kurikulum.view', 'kurikulum.manage'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        // 'admin' role holds ['*'] => all permissions; re-sync to pick up new ones
        Role::where('name', 'admin')->first()->syncPermissions(Permission::all());

        $this->tenant = Tenant::create(['nama' => 'Sekolah A', 'npsn' => '10000001']);
        app(TenantContext::class)->set($this->tenant->id);

        // Activate kurikulum plugin for this tenant
        $plugin = Plugin::updateOrCreate(
            ['kode' => 'kurikulum'],
            [
                'nama'           => 'Kurikulum',
                'versi'          => '1.0.0',
                'is_core'        => false,
                'aktif_global'   => true,
            ]
        );
        TenantPlugin::create([
            'tenant_id'  => $this->tenant->id,
            'plugin_id'  => $plugin->id,
            'aktif'      => true,
        ]);

        // Admin user with super_admin role (has kurikulum.manage permission)
        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'username'  => 'admin.test',
            'nama'      => 'Admin Test',
            'password'  => bcrypt('password'),
            'aktif'     => true,
            'tipe'      => 'admin_sekolah',
        ]);
        $this->admin->assignRole('admin');
    }

    protected function tearDown(): void
    {
        app(TenantContext::class)->clear();
        parent::tearDown();
    }

    public function test_index_lists_kurikulum_for_tenant(): void
    {
        Kurikulum::create(['kurikulum_id' => 'KURMER', 'nama_kurikulum' => 'Kurikulum Merdeka', 'status_aktif' => true]);
        Kurikulum::create(['kurikulum_id' => 'KUR13', 'nama_kurikulum' => 'Kurikulum 2013', 'status_aktif' => true]);

        $response = $this->actingAs($this->admin)->get('/kurikulum');

        $response->assertStatus(200);
        $response->assertSee('Kurikulum Merdeka');
        $response->assertSee('Kurikulum 2013');
    }

    public function test_store_creates_kurikulum(): void
    {
        $response = $this->actingAs($this->admin)->post('/kurikulum', [
            'kurikulum_id'   => 'KURMER',
            'nama_kurikulum' => 'Kurikulum Merdeka',
            'deskripsi'      => 'Kurikulum baru',
            'status_aktif'   => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kurikulum', [
            'kurikulum_id'   => 'KURMER',
            'nama_kurikulum' => 'Kurikulum Merdeka',
            'tenant_id'      => $this->tenant->id,
        ]);
    }

    public function test_store_validates_unique_kurikulum_id(): void
    {
        Kurikulum::create(['kurikulum_id' => 'KURMER', 'nama_kurikulum' => 'Existing', 'status_aktif' => true]);

        $response = $this->actingAs($this->admin)->post('/kurikulum', [
            'kurikulum_id'   => 'KURMER',
            'nama_kurikulum' => 'Duplicate',
            'status_aktif'   => true,
        ]);

        $response->assertSessionHasErrors(['kurikulum_id']);
    }

    public function test_update_modifies_kurikulum(): void
    {
        $kurikulum = Kurikulum::create(['kurikulum_id' => 'KURMER', 'nama_kurikulum' => 'Old Name', 'status_aktif' => true]);

        $response = $this->actingAs($this->admin)->put("/kurikulum/{$kurikulum->id}", [
            'kurikulum_id'   => 'KURMER',
            'nama_kurikulum' => 'New Name',
            'deskripsi'      => 'Updated',
            'status_aktif'   => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kurikulum', [
            'id'             => $kurikulum->id,
            'nama_kurikulum' => 'New Name',
        ]);
    }

    public function test_destroy_deletes_kurikulum(): void
    {
        $kurikulum = Kurikulum::create(['kurikulum_id' => 'KURMER', 'nama_kurikulum' => 'To Delete', 'status_aktif' => true]);

        $response = $this->actingAs($this->admin)->delete("/kurikulum/{$kurikulum->id}");

        $response->assertRedirect();
        // SoftDeletes — record masih ada tapi deleted_at terisi
        $this->assertSoftDeleted('kurikulum', ['id' => $kurikulum->id]);
    }

    public function test_tenant_isolation_blocks_cross_tenant_access(): void
    {
        // Kurikulum milik tenant A
        Kurikulum::create(['kurikulum_id' => 'KURMER', 'nama_kurikulum' => 'Tenant A', 'status_aktif' => true]);

        // Switch ke tenant B dan aktivasi plugin untuk tenant B juga
        $tenantB = Tenant::create(['nama' => 'Sekolah B', 'npsn' => '10000002']);
        app(TenantContext::class)->set($tenantB->id);

        $plugin = Plugin::where('kode', 'kurikulum')->first();
        TenantPlugin::create([
            'tenant_id'  => $tenantB->id,
            'plugin_id'  => $plugin->id,
            'aktif'      => true,
        ]);

        $userB = User::create([
            'tenant_id' => $tenantB->id,
            'username'  => 'admin.b',
            'nama'      => 'Admin B',
            'password'  => bcrypt('password'),
            'aktif'     => true,
            'tipe'      => 'admin_sekolah',
        ]);
        $userB->assignRole('admin');

        // Tenant B tidak boleh melihat kurikulum milik Tenant A di index
        $response = $this->actingAs($userB)->get('/kurikulum');
        $response->assertStatus(200);
        $response->assertDontSee('Tenant A');
    }
}
