# Role Waka Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tambahkan 3 role Waka (Wakil Kepala Sekolah) — `waka-kurikulum`, `waka-kesiswaan`, `waka-sarpras` — dengan mapping permission per bidang (manage di bidang sendiri, view-only di bidang lain).

**Architecture:** Tambah entri ke seeder Spatie Permission yang sudah ada (`RolePermissionSeeder.php` array `$roles` + `$permissions`) + demo user di `DemoSeeder.php`. Tanpa migrasi database, tanpa ubah controller/policy kecuali 1 fix kecil di `TabunganPolicy` (gap view-only tabungan). Menu auto-filter via `MenuRenderer::forUser()` berdasarkan permission.

**Tech Stack:** Laravel 11, Spatie Permission (teams mode, wildcard enabled), PHPUnit + RefreshDatabase, MySQL test DB `sisfokol_laravel_test`.

**Spec acuan:** `DEV_DOCS/073_dev_report_desain_role_waka_20260628.md`

---

## File Structure

| File | Aksi | Tanggung jawab |
|---|---|---|
| `database/seeders/RolePermissionSeeder.php` | Modify | Tambah 3 permission baru ke `$permissions` (baris 16-96) + 3 entri role ke `$roles` (baris 102-202) |
| `database/seeders/DemoSeeder.php` | Modify | Tambah 3 entri demo user ke array `$users` (sebelum foreach baris 141) |
| `app/Modules/Finance/Policies/TabunganPolicy.php` | Modify | Tambah `finance.student-saving.view` ke kondisi `viewAny` (fix gap view-only) |
| `tests/Feature/Auth/WakaRolePermissionTest.php` | Create | Test: 3 role ter-seed + mapping permission tepat |
| `tests/Feature/Auth/WakaDemoUserTest.php` | Create | Test: DemoSeeder membuat 3 user Waka dengan role benar |
| `tests/Feature/Auth/WakaViewOnlyGapTest.php` | Create | Test: permission gap-fix (jadwal view + tabungan view) ter-assign |

---

## Task 1: Tambah 3 Role Waka + Permission kurikulum ke RolePermissionSeeder

**Files:**
- Modify: `database/seeders/RolePermissionSeeder.php` (array `$permissions` baris 16-96, array `$roles` baris 102-202)
- Test: `tests/Feature/Auth/WakaRolePermissionTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Auth/WakaRolePermissionTest.php`:

```php
<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WakaRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_three_waka_roles_exist_after_seeding(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertNotNull(Role::findByName('waka-kurikulum', 'web'));
        $this->assertNotNull(Role::findByName('waka-kesiswaan', 'web'));
        $this->assertNotNull(Role::findByName('waka-sarpras', 'web'));
    }

    public function test_waka_kurikulum_can_manage_kurikulum_and_academic(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-kurikulum', 'web');

        $this->assertTrue($role->hasPermissionTo('kurikulum.manage'));
        $this->assertTrue($role->hasPermissionTo('master.classroom.*'));
        $this->assertTrue($role->hasPermissionTo('master.subject.*'));
        $this->assertTrue($role->hasPermissionTo('academic.schedule.*'));
        $this->assertTrue($role->hasPermissionTo('academic.curriculum.*'));
    }

    public function test_waka_kurikulum_is_view_only_outside_bidang(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-kurikulum', 'web');

        // View siswa (menu + policy)
        $this->assertTrue($role->hasPermissionTo('student.view'));
        $this->assertTrue($role->hasPermissionTo('siswa.view'));
        // View keuangan
        $this->assertTrue($role->hasPermissionTo('finance.student-bill.view'));
        $this->assertTrue($role->hasPermissionTo('tagihan.view'));
        // Tidak manage finance
        $this->assertFalse($role->hasPermissionTo('finance.*'));
    }

    public function test_waka_kesiswaan_can_manage_siswa_and_discipline(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-kesiswaan', 'web');

        $this->assertTrue($role->hasPermissionTo('student.*'));
        $this->assertTrue($role->hasPermissionTo('violation.*'));
        $this->assertTrue($role->hasPermissionTo('counseling.*'));
        $this->assertTrue($role->hasPermissionTo('achievement.*'));
        $this->assertTrue($role->hasPermissionTo('permit.*'));
    }

    public function test_waka_kesiswaan_cannot_manage_academic_or_finance(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-kesiswaan', 'web');

        $this->assertFalse($role->hasPermissionTo('master.classroom.*'));
        $this->assertFalse($role->hasPermissionTo('finance.*'));
        $this->assertTrue($role->hasPermissionTo('kelas.view'));
    }

    public function test_waka_sarpras_can_manage_inventory_and_room(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-sarpras', 'web');

        $this->assertTrue($role->hasPermissionTo('inventory.*'));
        $this->assertTrue($role->hasPermissionTo('master.room.*'));
        $this->assertTrue($role->hasPermissionTo('master.school-profile.update'));
    }

    public function test_waka_sarpras_is_view_only_outside_bidang(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findByName('waka-sarpras', 'web');

        $this->assertFalse($role->hasPermissionTo('student.*'));
        $this->assertFalse($role->hasPermissionTo('master.classroom.*'));
        $this->assertTrue($role->hasPermissionTo('student.view'));
        $this->assertTrue($role->hasPermissionTo('siswa.view'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Auth/WakaRolePermissionTest.php`
Expected: FAIL — `Role::findByName('waka-kurikulum')` throws `RoleDoesNotExist` (role belum di-seed).

- [ ] **Step 3: Tambah permission baru ke array `$permissions`**

Di `database/seeders/RolePermissionSeeder.php`, tambah 3 permission baru ini ke array `$permissions` (setelah baris 95, sebelum `];` baris 96). Catatan: `kurikulum.view`/`kurikulum.manage` idempotent dengan plugin Kurikulum (`Permission::firstOrCreate`); `finance.student-saving.view` untuk fix gap tabungan di Task 3.

```php
            // Waka — kurikulum plugin permissions (idempotent dengan app/Plugins/Kurikulum/permissions.php)
            'kurikulum.view',
            'kurikulum.manage',

            // Waka — view-only tabungan (fix gap TabunganPolicy, lihat Task 3)
            'finance.student-saving.view',
```

- [ ] **Step 4: Tambah 3 entri role Waka ke array `$roles`**

Di `database/seeders/RolePermissionSeeder.php`, tambah 3 entri ini ke array `$roles` (setelah entri `'inventory'`, sebelum `];` baris 202). Mapping lengkap dari spec §4 (DEV_DOCS-073), sudah mencakup fix gap jadwal (`academic.schedule.view` untuk kesiswaan/sarpras) dan tabungan (`finance.student-saving.view`):

```php
            'waka-kurikulum' => [
                // Manage: Kurikulum & Akademik
                'kurikulum.manage', 'kurikulum.view',
                'master.subject.*', 'master.subject-type.*', 'mapel.view',
                'master.classroom.*', 'kelas.view',
                'master.academic-year.*',
                'master.room.*',
                'master.extracurricular.*',
                'academic.schedule.*', 'jadwal.view',
                'academic.curriculum.*', 'academic.teacher-agenda.*',
                // View: bidang lain
                'student.view', 'siswa.view',
                'employee.view', 'guru.view',
                'presence.view', 'presensi.view',
                'absence.view', 'absensi.view',
                'finance.student-bill.view', 'tagihan.view',
                'finance.student-payment.view', 'pembayaran.view',
                'finance.student-saving.view', 'tabungan.view',
                'raport.view',
                'dashboard.view', 'report.*', 'master.school-profile.view',
            ],
            'waka-kesiswaan' => [
                // Manage: Kesiswaan, Organisasi
                'student.*', 'siswa.view',
                'violation.*', 'master.violation-type.*', 'master.violation-point.*',
                'counseling.*', 'master.counseling-type.*',
                'achievement.*', 'master.achievement-type.*',
                'permit.*',
                'absence.*', 'absensi.view',
                'master.extracurricular.*',
                // View: bidang lain
                'kurikulum.view',
                'employee.view', 'guru.view', 'kelas.view', 'mapel.view',
                'academic.schedule.view', 'jadwal.view',
                'presence.view', 'presensi.view',
                'finance.student-bill.view', 'tagihan.view',
                'finance.student-payment.view', 'pembayaran.view',
                'finance.student-saving.view', 'tabungan.view',
                'raport.view',
                'dashboard.view', 'report.*', 'master.school-profile.view',
            ],
            'waka-sarpras' => [
                // Manage: Sarana Prasarana
                'inventory.*',
                'master.room.*',
                'master.school-profile.update', 'master.school-profile.view',
                // View: bidang lain
                'student.view', 'siswa.view', 'employee.view', 'guru.view',
                'kelas.view', 'mapel.view',
                'academic.schedule.view', 'jadwal.view',
                'presence.view', 'presensi.view', 'absence.view', 'absensi.view',
                'finance.student-bill.view', 'tagihan.view',
                'finance.student-payment.view', 'pembayaran.view',
                'finance.student-saving.view', 'tabungan.view',
                'raport.view', 'kurikulum.view',
                'dashboard.view', 'report.*',
            ],
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test tests/Feature/Auth/WakaRolePermissionTest.php`
Expected: PASS — 7 tests, 0 fail.

- [ ] **Step 6: Commit**

```bash
git add database/seeders/RolePermissionSeeder.php tests/Feature/Auth/WakaRolePermissionTest.php
git commit -m "feat(rbac): tambah 3 role Waka + permission kurikulum/tabungan-view"
```

---

## Task 2: Tambah 3 Demo User Waka ke DemoSeeder

**Files:**
- Modify: `database/seeders/DemoSeeder.php` (array `$users` sebelum foreach baris 141)
- Test: `tests/Feature/Auth/WakaDemoUserTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Auth/WakaDemoUserTest.php`:

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WakaDemoUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_three_waka_users_with_roles(): void
    {
        $this->seed([RolePermissionSeeder::class, SuperAdminSeeder::class]);
        $this->seed(DemoSeeder::class);

        $this->assertDatabaseHas('users', ['username' => 'waka.kurikulum.demo']);
        $this->assertDatabaseHas('users', ['username' => 'waka.kesiswaan.demo']);
        $this->assertDatabaseHas('users', ['username' => 'waka.sarpras.demo']);

        $kurikulum = User::where('username', 'waka.kurikulum.demo')->first();
        $kesiswaan = User::where('username', 'waka.kesiswaan.demo')->first();
        $sarpras = User::where('username', 'waka.sarpras.demo')->first();

        $this->assertTrue($kurikulum->hasRole('waka-kurikulum'));
        $this->assertTrue($kesiswaan->hasRole('waka-kesiswaan'));
        $this->assertTrue($sarpras->hasRole('waka-sarpras'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Auth/WakaDemoUserTest.php`
Expected: FAIL — `assertDatabaseHas('users', ['username' => 'waka.kurikulum.demo'])` fails (user belum dibuat seeder).

- [ ] **Step 3: Tambah 3 entri ke array `$users` di DemoSeeder**

Di `database/seeders/DemoSeeder.php`, tambah 3 entri ini ke array `$users` (setelah entri `walikelas.demo` / sebelum `teacher` lain sesuai struktur; pola sama dengan baris 128-134). Format persis field yang dipakai loop baris 141-180 (`username`, `nama`, `email`, `tipe`, `role`):

```php
            [
                'username' => 'waka.kurikulum.demo',
                'nama'     => 'Waka Kurikulum Demo',
                'email'    => 'wakakurikulum@smademo.sch.id',
                'tipe'     => 'pegawai',
                'role'     => 'waka-kurikulum',
            ],
            [
                'username' => 'waka.kesiswaan.demo',
                'nama'     => 'Waka Kesiswaan Demo',
                'email'    => 'wakakesiswaan@smademo.sch.id',
                'tipe'     => 'pegawai',
                'role'     => 'waka-kesiswaan',
            ],
            [
                'username' => 'waka.sarpras.demo',
                'nama'     => 'Waka Sarpras Demo',
                'email'    => 'wakasarpras@smademo.sch.id',
                'tipe'     => 'pegawai',
                'role'     => 'waka-sarpras',
            ],
```

Loop foreach baris 141 otomatis membuat User + assignRole + Employee + Guru + userable link — tidak perlu kode tambahan.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Auth/WakaDemoUserTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add database/seeders/DemoSeeder.php tests/Feature/Auth/WakaDemoUserTest.php
git commit -m "feat(demo): tambah 3 demo user Waka + record Employee/Guru"
```

---

## Task 3: Fix Gap View-Only (TabunganPolicy) + Verifikasi Jadwal

> **Konteks:** Spec §6 menandai gap: `TabunganPolicy::viewAny` hanya terima `finance.*` || `finance.student-saving.*` (tidak ada view-only). Akibatnya role view-only (termasuk `principal` existing) lihat menu tabungan tapi 403 di data. Fix: tambah `finance.student-saving.view` (sudah ditambah ke `$permissions` di Task 1 Step 3, sudah di-assign ke 3 waka di Task 1 Step 4). Tinggal update policy. Jadwal gap SUDAH ditangani di Task 1 (`academic.schedule.view` untuk kesiswaan/sarpras) — task ini verifikasi saja.

**Files:**
- Modify: `app/Modules/Finance/Policies/TabunganPolicy.php` (method `viewAny` baris 12-16)
- Test: `tests/Feature/Auth/WakaViewOnlyGapTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Auth/WakaViewOnlyGapTest.php`:

```php
<?php

namespace Tests\Feature\Auth;

use App\Modules\Academic\Models\Jadwal;
use App\Modules\Finance\Models\TabunganSiswa;
use App\Modules\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WakaViewOnlyGapTest extends TestCase
{
    use RefreshDatabase;

    public function test_waka_kesiswaan_has_jadwal_view_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $tenant = Tenant::create(['nama' => 'T', 'npsn' => '70000001']);
        $waka = User::factory()->create(['tenant_id' => $tenant->id, 'tipe' => 'pegawai']);
        $waka->assignRole('waka-kesiswaan');

        // Permission view-only jadwal ter-assign (fix Task 1)
        $this->assertTrue($waka->can('academic.schedule.view'));
        $this->assertFalse($waka->can('academic.schedule.*'));
    }

    public function test_waka_roles_have_tabungan_view_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $tenant = Tenant::create(['nama' => 'T', 'npsn' => '70000002']);
        $waka = User::factory()->create(['tenant_id' => $tenant->id, 'tipe' => 'pegawai']);
        $waka->assignRole('waka-kurikulum');

        // Permission view-only tabungan ter-assign (Task 1) — policy belum accept → fix di step 3
        $this->assertTrue($waka->can('finance.student-saving.view'));
    }

    public function test_tabungan_policy_viewany_accepts_view_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $tenant = Tenant::create(['nama' => 'T', 'npsn' => '70000003']);
        $waka = User::factory()->create(['tenant_id' => $tenant->id, 'tipe' => 'pegawai']);
        $waka->assignRole('waka-kurikulum');

        // Policy viewAny harus return true untuk waka view-only (setelah fix)
        $this->assertTrue(
            \Illuminate\Support\Facades\Gate::forUser($waka)->allows('viewAny', TabunganSiswa::class)
        );
    }
}
```

- [ ] **Step 2: Run test to verify failure point**

Run: `php artisan test tests/Feature/Auth/WakaViewOnlyGapTest.php`
Expected: Test 1 & 2 PASS (permission ter-assign dari Task 1). Test 3 (`test_tabungan_policy_viewany_accepts_view_permission`) FAIL — `TabunganPolicy::viewAny` belum accept `finance.student-saving.view`.

- [ ] **Step 3: Update TabunganPolicy::viewAny untuk accept view-only**

Di `app/Modules/Finance/Policies/TabunganPolicy.php`, ubah method `viewAny` (baris 12-16) dari:

```php
    public function viewAny(User $user): bool
    {
        return $user->can('finance.*') 
            || $user->can('finance.student-saving.*');
    }
```

menjadi:

```php
    public function viewAny(User $user): bool
    {
        return $user->can('finance.*')
            || $user->can('finance.student-saving.*')
            || $user->can('finance.student-saving.view');
    }
```

> Catatan: `view`, `create`, `update` tetap pakai `finance.* || finance.student-saving.*` (hanya manage yang boleh). Hanya `viewAny` yang diperluas untuk view-only.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Auth/WakaViewOnlyGapTest.php`
Expected: PASS — 3 tests.

- [ ] **Step 5: Commit**

```bash
git add app/Modules/Finance/Policies/TabunganPolicy.php tests/Feature/Auth/WakaViewOnlyGapTest.php
git commit -m "fix(rbac): TabunganPolicy viewAny accept finance.student-saving.view (gap view-only)"
```

---

## Task 4: Regression Test Suite + Manual Smoke

**Files:** tidak ada (verifikasi saja)

- [ ] **Step 1: Jalankan full test suite (pastikan tidak ada regresi)**

Run: `php artisan test`
Expected: ALL PASS. Sebelumnya 45 tests (DEV_DOCS-072) + 7 (Task 1) + 1 (Task 2) + 3 (Task 3) = ~56 tests, 0 fail. Khususnya `tests/Feature/Finance/TabunganMutasiTest` tetap lulus (tidak terdampak perubahan viewAny).

- [ ] **Step 2: Re-seed DB dev & verifikasi manual smoke**

Run: `php artisan migrate:fresh --seed`
Lalu login sebagai 3 demo user Waka (`waka.kurikulum.demo` / `waka.kesiswaan.demo` / `waka.sarpras.demo`, password `demo1234`) dan verifikasi di browser:

- **waka-kurikulum**: menu Kurikulum/Kelas/Mapel/Jadwal muncul & CRUD berhasil; menu Tagihan/Tabungan muncul (view) tapi tombol create/edit tidak ada / 403 saat aksi.
- **waka-kesiswaan**: menu Siswa/Pelanggaran/BK/Prestasi muncul & CRUD berhasil; menu Kurikulum/Kelas muncul (view-only).
- **waka-sarpras**: menu Inventaris/Ruang muncul & CRUD berhasil; menu lain view-only.

Jika ada menu yang 403 padahal seharusnya view → catat, tambah permission view yang relevan (cek policy modul terkait, pola sama dengan Task 3).

- [ ] **Step 3: Commit hasil verifikasi (opsional, jika ada perbaikan)**

Jika Step 2 menemukan gap tambahan, fix + tambah test + commit. Jika bersih, tidak ada commit.

```bash
git add -A
git commit -m "test(rbac): verifikasi smoke 3 role Waka"
```

---

## Self-Review

**1. Spec coverage (DEV_DOCS-073):**
- §3 keputusan 3 role terpisah → Task 1 ✓
- §4.1/4.2/4.3 mapping permission → Task 1 Step 4 (3 array lengkap) ✓
- §5 file RolePermissionSeeder + DemoSeeder → Task 1 + Task 2 ✓
- §6 gap tabungan → Task 3 ✓
- §6 gap Mapel/Jadwal → jadwal ditangani Task 1 (`academic.schedule.view`); mapel tidak ada MapelPolicy (route-middleware via `mapel.view`, semua waka punya) → verifikasi Task 4 Step 2 ✓
- §6 rapor manage (tidak ada permission) → di luar scope, sesuai spec ✓
- §6 label tampilan role → tetap terbuka (opsional impl), tidak ada task — **sesuai spec (keputusan impl, bukan arsitektur)**

**2. Placeholder scan:** Tidak ada TBD/TODO. Semua step berisi kode lengkap atau command konkret. ✓

**3. Type/konsistensi nama:** Nama role konsisten (`waka-kurikulum`/`waka-kesiswaan`/`waka-sarpras`) di test, seeder, dan demo user. Nama permission (`finance.student-saving.view`, `academic.schedule.view`, `kurikulum.manage`) konsisten dengan seeder `$permissions` + policy. ✓

---

## Execution Handoff

Plan complete and saved to `DEV_DOCS/074_dev_report_plan_role_waka_20260628.md`. Two execution options:

**1. Subagent-Driven (recommended)** — dispatch fresh subagent per task, review antar task, iterasi cepat.

**2. Inline Execution** — eksekusi task di sesi ini via executing-plans, batch dengan checkpoint review.

Which approach?
