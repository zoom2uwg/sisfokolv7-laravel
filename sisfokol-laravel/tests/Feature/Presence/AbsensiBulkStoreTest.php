<?php

namespace Tests\Feature\Presence;

use App\Models\AcademicYear;
use App\Models\Absence;
use App\Models\User;
use App\Modules\Academic\Models\Guru;
use App\Modules\Academic\Models\Kelas;
use App\Modules\Academic\Models\KelasSiswa;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiBulkStoreTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Kelas $kelas;
    private Siswa $siswaHadir;
    private Siswa $siswaAlpha;
    private User $piket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['nama' => 'Sekolah A', 'npsn' => '10000001']);
        app(TenantContext::class)->set($this->tenant->id);

        AcademicYear::create(['name' => '2025/2026', 'is_active' => true]);

        $guru = Guru::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->kelas = Kelas::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'wali_kelas_id'  => $guru->id,
        ]);

        $this->siswaHadir = Siswa::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'aktif']);
        $this->siswaAlpha = Siswa::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'aktif']);

        KelasSiswa::create([
            'tenant_id'        => $this->tenant->id,
            'kelas_id'         => $this->kelas->id,
            'siswa_id'         => $this->siswaHadir->id,
            'tahun_ajaran_id'  => AcademicYear::first()->id,
            'no_urut'          => 1,
        ]);
        KelasSiswa::create([
            'tenant_id'        => $this->tenant->id,
            'kelas_id'         => $this->kelas->id,
            'siswa_id'         => $this->siswaAlpha->id,
            'tahun_ajaran_id'  => AcademicYear::first()->id,
            'no_urut'          => 2,
        ]);

        $this->piket = User::create([
            'tenant_id' => $this->tenant->id,
            'username'  => 'piket001',
            'nama'      => 'Pak Piket',
            'tipe'      => 'pegawai',
            'password'  => bcrypt('password'),
            'aktif'     => true,
        ]);
    }

    protected function tearDown(): void
    {
        app(TenantContext::class)->clear();
        parent::tearDown();
    }

    public function test_bulk_store_creates_absence_only_for_non_hadir_status(): void
    {
        $response = $this->actingAs($this->piket)->post('/presence/absensi', [
            'date'     => '2026-06-28',
            'kelas_id' => $this->kelas->id,
            'status'   => [
                $this->siswaHadir->id => 'hadir',
                $this->siswaAlpha->id => 'alpha',
            ],
        ]);

        $response->assertRedirect(route('presence.absensi.index'));

        // "hadir" → tidak ada record Absence
        $this->assertEquals(0, Absence::where('absentable_id', $this->siswaHadir->id)->count());

        // "alpha" → ada record Absence dengan type "alpha"
        $absence = Absence::where('absentable_id', $this->siswaAlpha->id)->first();
        $this->assertNotNull($absence);
        $this->assertEquals('alpha', $absence->type);
        $this->assertEquals(Siswa::class, $absence->absentable_type);
    }

    public function test_bulk_store_is_idempotent_overwrites_same_date(): void
    {
        // Store pertama: siswaAlpha = alpha
        $this->actingAs($this->piket)->post('/presence/absensi', [
            'date'     => '2026-06-28',
            'kelas_id' => $this->kelas->id,
            'status'   => [$this->siswaAlpha->id => 'alpha'],
        ]);
        $this->assertEquals(1, Absence::where('absentable_id', $this->siswaAlpha->id)->count());

        // Store kedua untuk tanggal yg sama: siswaAlpha = sakit (overwrite)
        $this->actingAs($this->piket)->post('/presence/absensi', [
            'date'     => '2026-06-28',
            'kelas_id' => $this->kelas->id,
            'status'   => [$this->siswaAlpha->id => 'sakit'],
        ]);

        // Tidak ada duplikasi — record lama dihapus, record baru dibuat
        $absences = Absence::where('absentable_id', $this->siswaAlpha->id)->get();
        $this->assertCount(1, $absences);
        $this->assertEquals('sick', $absences->first()->type);
    }

    public function test_bulk_store_maps_status_to_type_correctly(): void
    {
        $this->actingAs($this->piket)->post('/presence/absensi', [
            'date'     => '2026-06-28',
            'kelas_id' => $this->kelas->id,
            'status'   => [
                $this->siswaHadir->id => 'ijin',
                $this->siswaAlpha->id => 'sakit',
            ],
        ]);

        // ijin → permission, sakit → sick
        $this->assertEquals('permission', Absence::where('absentable_id', $this->siswaHadir->id)->first()->type);
        $this->assertEquals('sick', Absence::where('absentable_id', $this->siswaAlpha->id)->first()->type);
    }
}
