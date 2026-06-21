<?php

namespace Tests\Feature\Plugin;

use App\Models\User;
use App\Modules\Academic\Models\{Kelas, Mapel, Siswa};
use App\Modules\Evaluation\Events\EvaluationResolveFramework;
use App\Modules\Evaluation\Services\EvaluationFrameworkResolver;
use App\Modules\Tenancy\Models\Tenant;
use App\Plugins\Kurikulum\Models\{Kurikulum, StrukturKurikulum, KomponenKompetensi};
use App\Plugins\Kurikulum\Providers\KurikulumServiceProvider;
use App\Support\TenantContext;
use Database\Seeders\{RolePermissionSeeder, SuperAdminSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class KurikulumPluginTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluation_framework_event_resolves_via_kurikulum(): void
    {
        $this->seed([RolePermissionSeeder::class, SuperAdminSeeder::class]);
        $this->app->register(KurikulumServiceProvider::class);

        [$tenant, $mapel, $kelas, $kurikulum] = $this->setupScenario();

        $resolver = app(EvaluationFrameworkResolver::class);
        $framework = $resolver->resolve($mapel, $kelas);

        $this->assertNotNull($framework);
        $this->assertSame('Kurikulum Merdeka', $framework['kurikulum']);
        $this->assertSame('D', $framework['fase']);
        $this->assertContains('KI-3', $framework['ki']);
        $this->assertSame('deep_learning', $framework['pedagogis']);
    }

    public function test_no_framework_when_mapel_has_no_kurikulum_id(): void
    {
        $this->app->register(KurikulumServiceProvider::class);
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        app(TenantContext::class)->set(tenantId: $tenant->id);

        $plugin = \App\Plugins\Infrastructure\Models\Plugin::updateOrCreate(
            ['kode' => 'kurikulum'],
            [
                'nama' => 'Kurikulum',
                'versi' => '1.0.0',
                'is_core' => false,
                'provider_class' => \App\Plugins\Kurikulum\Providers\KurikulumServiceProvider::class,
                'aktif_global' => true,
            ]
        );
        \App\Plugins\Infrastructure\Models\TenantPlugin::create([
            'tenant_id' => $tenant->id,
            'plugin_id' => $plugin->id,
            'aktif' => true,
        ]);

        $mapel = Mapel::create(['kode' => 'MTH', 'nama' => 'Matematika', 'kkm' => 75, 'tenant_id' => $tenant->id, 'kurikulum_id' => null]);

        $framework = app(EvaluationFrameworkResolver::class)->resolve($mapel);
        $this->assertNull($framework);
    }

    public function test_kurikulum_can_be_activated_and_seeds_permissions(): void
    {
        $this->seed([RolePermissionSeeder::class]);
        
        $tenant = Tenant::create(['nama' => 'Sekolah Test', 'npsn' => '12345678']);
        $admin = User::create([
            'tenant_id' => $tenant->id,
            'username' => 'admin.test',
            'nama' => 'Admin Test',
            'password' => bcrypt('password'),
            'aktif' => true,
            'tipe' => 'admin_sekolah',
        ]);
        $admin->assignRole('admin');

        app(TenantContext::class)->set($tenant->id);

        $plugin = \App\Plugins\Infrastructure\Models\Plugin::updateOrCreate(
            ['kode' => 'kurikulum'],
            [
                'nama' => 'Kurikulum',
                'versi' => '1.0.0',
                'is_core' => false,
                'provider_class' => \App\Plugins\Kurikulum\Providers\KurikulumServiceProvider::class,
                'aktif_global' => true,
            ]
        );

        $response = $this->actingAs($admin)->post("/admin/plugins/kurikulum/activate");
        $response->assertRedirect();

        // Permissions should be seeded
        $this->assertTrue(\Spatie\Permission\Models\Permission::where('name', 'kurikulum.view')->exists());
        $this->assertTrue(\Spatie\Permission\Models\Permission::where('name', 'kurikulum.manage')->exists());
    }

    private function setupScenario(): array
    {
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        app(TenantContext::class)->set(tenantId: $tenant->id);

        $plugin = \App\Plugins\Infrastructure\Models\Plugin::updateOrCreate(
            ['kode' => 'kurikulum'],
            [
                'nama' => 'Kurikulum',
                'versi' => '1.0.0',
                'is_core' => false,
                'provider_class' => \App\Plugins\Kurikulum\Providers\KurikulumServiceProvider::class,
                'aktif_global' => true,
            ]
        );
        \App\Plugins\Infrastructure\Models\TenantPlugin::create([
            'tenant_id' => $tenant->id,
            'plugin_id' => $plugin->id,
            'aktif' => true,
        ]);

        app(\App\Support\PluginRegistry::class)->clearTenantCache($tenant->id, 'kurikulum');

        $kurikulum = Kurikulum::create(['kurikulum_id' => 'KURMER', 'nama_kurikulum' => 'Kurikulum Merdeka', 'status_aktif' => true, 'tenant_id' => $tenant->id]);
        $struktur = StrukturKurikulum::create(['kurikulum_id' => $kurikulum->id, 'jenjang' => 'SMP', 'kelas' => '7', 'fase' => 'D', 'jenis_kegiatan' => 'intrakurikuler', 'tenant_id' => $tenant->id]);
        KomponenKompetensi::create(['struktur_id' => $struktur->id, 'kode_kompetensi' => 'KI-3', 'teks_kompetensi' => 'Memahami...', 'pendekatan_pedagogis' => 'deep_learning', 'tenant_id' => $tenant->id]);
        $mapel = Mapel::create(['kode' => 'MTH', 'nama' => 'Matematika', 'kkm' => 75, 'tenant_id' => $tenant->id, 'kurikulum_id' => $kurikulum->id]);
        $kelas = Kelas::create(['nama' => '7-A', 'tingkat' => 7, 'tenant_id' => $tenant->id]);
        return [$tenant, $mapel, $kelas, $kurikulum];
    }
}
