<?php

namespace Tests\Feature\Presence;

use App\Models\Permit;
use App\Models\User;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Presence\Services\IzinApprovalService;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinApprovalTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Siswa $siswa;
    private User $picketOfficer;
    private User $counselor;
    private IzinApprovalService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(IzinApprovalService::class);

        $this->tenant = Tenant::create(['nama' => 'Sekolah A', 'npsn' => '10000001']);

        app(TenantContext::class)->set($this->tenant->id);

        $this->siswa = Siswa::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status'    => 'aktif',
        ]);

        // Picket officer user
        $this->picketOfficer = User::create([
            'tenant_id' => $this->tenant->id,
            'username'  => 'picket001',
            'nama'      => 'Pak Piket',
            'email'     => 'picket@sekolah.sch.id',
            'tipe'      => 'pegawai',
            'password'  => bcrypt('password'),
            'aktif'     => true,
        ]);

        // Counselor user
        $this->counselor = User::create([
            'tenant_id' => $this->tenant->id,
            'username'  => 'bk001',
            'nama'      => 'Bu BK',
            'email'     => 'bk@sekolah.sch.id',
            'tipe'      => 'pegawai',
            'password'  => bcrypt('password'),
            'aktif'     => true,
        ]);

        app(TenantContext::class)->clear();
    }

    public function test_picket_officer_can_submit_izin_for_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant->id);

        $permit = $this->service->submit([
            'permitable_type' => Siswa::class,
            'permitable_id'   => $this->siswa->id,
            'date'            => today()->toDateString(),
            'type'            => 'sick',
            'reason'          => 'Demam dan flu',
        ], $this->picketOfficer);

        $this->assertNotNull($permit);
        $this->assertEquals('pending', $permit->status);
        $this->assertEquals($this->tenant->id, $permit->tenant_id);
        $this->assertEquals('sick', $permit->type);

        app(TenantContext::class)->clear();
    }

    public function test_counselor_can_approve_izin(): void
    {
        app(TenantContext::class)->set($this->tenant->id);

        $permit = $this->service->submit([
            'permitable_type' => Siswa::class,
            'permitable_id'   => $this->siswa->id,
            'date'            => today()->toDateString(),
            'type'            => 'sick',
            'reason'          => 'Demam',
        ], $this->picketOfficer);

        $approved = $this->service->approve($permit, $this->counselor);

        $this->assertEquals('approved', $approved->status);
        $this->assertEquals($this->counselor->id, $approved->approved_by);
        $this->assertNotNull($approved->approved_at);

        app(TenantContext::class)->clear();
    }

    public function test_izin_can_be_rejected(): void
    {
        app(TenantContext::class)->set($this->tenant->id);

        $permit = $this->service->submit([
            'permitable_type' => Siswa::class,
            'permitable_id'   => $this->siswa->id,
            'date'            => today()->toDateString(),
            'type'            => 'permission',
            'reason'          => 'Urusan keluarga',
        ], $this->picketOfficer);

        $rejected = $this->service->reject($permit, $this->counselor, 'Tidak ada surat resmi');

        $this->assertEquals('rejected', $rejected->status);
        $this->assertEquals($this->counselor->id, $rejected->approved_by);

        app(TenantContext::class)->clear();
    }

    public function test_cannot_approve_already_processed_izin(): void
    {
        app(TenantContext::class)->set($this->tenant->id);

        $permit = $this->service->submit([
            'permitable_type' => Siswa::class,
            'permitable_id'   => $this->siswa->id,
            'date'            => today()->toDateString(),
            'type'            => 'sick',
            'reason'          => 'Sakit',
        ], $this->picketOfficer);

        $this->service->approve($permit, $this->counselor);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Izin ini sudah diproses sebelumnya.');

        $this->service->approve($permit->fresh(), $this->counselor);

        app(TenantContext::class)->clear();
    }
}
