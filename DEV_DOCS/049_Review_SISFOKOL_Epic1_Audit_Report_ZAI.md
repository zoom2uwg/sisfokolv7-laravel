# SISFOKOL v7 — Audit Mendalam Epic 1 (Setup & Fondasi)
**Tanggal Audit:** 2026-06-22  
**Penulis** : ZAI (Z-Code AI)
**Repo:** https://github.com/haisyamalawwab/sisfokolv7  
**Lokasi Workspace:** `/home/z/my-project/sisfokolv7/`  
**Acuan:** `DEV_DOCS/013_walkthrough_epic_1_setup_fondasi_20260620_2327.md` & `DEV_DOCS/012_implementation.md` & `sisfokol-laravel/docs/design.md`

---

## 1. Ringkasan Eksekutif

**Klaim DEV_DOCS-013:** Epic 1 (Setup + Fondasi) ✅ SELESAI, 19 tests pass, 100% green.  
**Realita Fisik (audit per-file, jalankan `composer install`, `migrate`, `db:seed`, `php artisan test`, dan serve HTTP):**

| Aspek | Status | Bukti Fisik |
|---|---|---|
| File fondasi Epic 1 ada | ✅ 30/30 OK | TenantContext, helpers, BelongsToTenant, TracksAuditColumns, ResolveTenant, SuperAdminSeeder, RolePermissionSeeder, MenuSeeder, FieldSeeder, 4 tenancy migrations, 4 RBAC migrations, 1 plugin migration |
| `composer install` | ✅ Sukses | 132 packages, Laravel 11.31, Spatie 6.25, lab404 1.7 |
| `php artisan migrate` | ✅ 79 migrations | Semua migration berjalan tanpa error (di SQLite; perlu MySQL untuk production) |
| `php artisan db:seed` | ✅ 14 seeders | 27 users, 1 tenant, 10 roles, 43 permissions, 18 menus, 10 fields |
| `php artisan test` | ⚠️ 112/115 pass | 2 fail karena MySQL driver tidak ada di env ini; 1 fail asli di QrScanTest |
| Login page render | ✅ HTTP 200, 8 KB | `/login` menampilkan form login Bootstrap 5 |
| Auth flow superadmin | ✅ Berhasil | `Auth::attempt(['username'=>'superadmin','password'=>'SuperAdmin#2026'])` → success; `isSuperAdmin()=true`, `hasRole('super_admin')=true` |
| **GAP KRITIS 1: CurriculumController missing** | ❌ BREAKING | `app/Modules/Evaluation/routes.php:5,24-26` me-`use` controller yang file-nya TIDAK ADA. Membuat `php artisan serve` crash. |
| **GAP KRITIS 2: 17/18 menu → permission tidak ada** | ❌ Menu tidak tampil | `MenuSeeder` mereferensikan `dashboard.view`, `tenant.view`, `user.manage`, `rbac.manage`, `audit.view`, `siswa.view`, `guru.view`, `kelas.view`, `mapel.view`, `jadwal.view`, `tagihan.view`, `pembayaran.view`, `tabungan.view`, `presensi.view`, `raport.view` — **semuanya tidak terdaftar di `RolePermissionSeeder`** |
| **GAP KRITIS 3: super_admin role = 0 permissions** | ❌ Tidak ada Gate::before | `AuthServiceProvider::boot()` kosong — tidak ada `Gate::before(fn $user => $user->isSuperAdmin())`. SuperAdmin hanya bisa akses route yang tidak cek permission. |
| **GAP STRUKTURAL: Skema duplikat** | ⚠️ 107 tabel vs design 48 | `database/migrations/` (legacy English plural: students, employees, schedules, …) **berdampingan** dengan `app/Modules/*/Database/Migrations/` (modular Indonesia singular: siswa, guru, jadwal, …). Kedua set tabel live di DB yang sama. |
| **GAP LINGKUNGAN: .env.example vs design** | ⚠️ Default SQLite, no legacy DB | Design doc & DEV_DOCS menyatakan MySQL InnoDB `sisfokol_laravel` + legacy `sisfokol_v7`. `.env.example` default `DB_CONNECTION=sqlite` dan tidak punya block legacy. `DatabaseConnectionTest::it_can_connect_to_default_database` & `it_can_connect_to_legacy_database` pasti gagal di fresh install. |

**Verdict:** Klaim "Epic 1 selesai 100%" **setengah benar**. Fondasi (trait, middleware, seeder inti) **memang ada dan berfungsi**. Tetapi terdapat **3 gap kritis** dan **2 gap struktural** yang membuat aplikasi **TIDAK bisa berjalan end-to-end tanpa patch** di kondisi repo saat ini.

---

## 2. Metodologi Audit

1. **Clone repo** ke `/home/z/my-project/sisfokolv7/` (88 commit, sejak 2026-06-20).
2. **Baca DEV_DOCS-012** (Implementation Plan status), **DEV_DOCS-013** (Walkthrough Epic 1), dan **`docs/design.md`** sebagai sumber kebenaran.
3. **Pemetaan fisik per file** di direktori `sisfokol-laravel/` — verifikasi setiap item yang dijanjikan Epic 1 ada di path yang benar.
4. **Audit sejarah git** untuk menelusuri kapan dan bagaimana implementasi dilakukan.
5. **Setup PHP 8.4 + composer** (tanpa sudo — ekstrak `.deb` ke `/home/z/php-root/`), install extensions (gd, zip, mbstring, pdo_sqlite, intl, …).
6. **`composer install`** (132 packages, sukses setelah `ext-gd` dan `ext-zip` diaktifkan).
7. **Konfigurasi `.env`** untuk SQLite (karena MySQL tidak tersedia tanpa sudo), generate `APP_KEY`.
8. **`php artisan migrate`** → 79 migration sukses.
9. **`php artisan db:seed`** → 14 seeder sukses dengan output tabel kredensial demo.
10. **`php artisan test`** (PHPUnit 11.5.55) → 115 tests, 112 pass, 2 env errors, 1 real failure.
11. **`php artisan serve`** → gagal karena `CurriculumController` missing; setelah patch sementara → server jalan, `/login` render 200.
12. **Auth flow test** via HTTP kernel langsung (bypass throttle:5,1) → `Auth::attempt` sukses untuk superadmin & admin.sekolah.

---

## 3. Pemetaan Komponen Epic 1 — Verifikasi Per-File

### 3.1 File Fondasi (Task 4 Epic 1)

| File | Status | Catatan |
|---|---|---|
| `app/Support/TenantContext.php` | ✅ Ada (49 baris) | Singleton `tenantId/branchId/settings`, `isSuperAdminContext()` benar. Match spec ADR-003. |
| `app/Models/Traits/BelongsToTenant.php` | ✅ Ada (32 baris) | Global scope `tenant_id` + auto-fill on create. Match spec. |
| `app/Models/Traits/TracksAuditColumns.php` | ✅ Ada (29 baris) | Auto `created_by`/`updated_by` dari `auth()->id()`. Match ADR-007. |
| `app/Support/helpers.php` | ✅ Ada (142 baris) | Berisi `tenant_and_audit_columns()`, `audit_columns()`, `clean_money()`, `clean_date()`, `clean_phone()`, `carbon_month_name()`. Lebih lengkap dari spec. |

### 3.2 Middleware (Task 5 Epic 1)

| File | Status | Catatan |
|---|---|---|
| `app/Http/Middleware/ResolveTenant.php` | ✅ Ada | Resolve tenant dari `auth()->user()->tenant_id`, load settings ke `TenantContext`. |
| `app/Http/Middleware/ForcePasswordReset.php` | ✅ Ada | Paksa redirect ke `/password/change` jika `must_reset_password=true`. |
| `app/Http/Middleware/BlockWhileImpersonating.php` | ✅ Ada | Block aksi sensitif saat sesi impersonasi aktif. |
| `app/Http/Middleware/EnsurePluginEnabled.php` | ✅ Ada (Epic 4) | Cek `PluginRegistry::isActiveForTenant()`. |
| `bootstrap/app.php` | ✅ Ada | Semua alias middleware terdaftar: `role`, `permission`, `role_or_permission`, `tenant`, `force.reset`, `impersonate.block`, `plugin`. Match DEV_DOCS-013. |

### 3.3 Providers (Task 5 Epic 1)

| File | Status | Catatan |
|---|---|---|
| `app/Providers/AppServiceProvider.php` | ✅ Ada | Singleton `TenantContext` + `AuditLogger`, register policies (Siswa, Guru, Kelas, Jadwal, AuditLog, Attendance, Permit), register Blade directives, Blueprint macros. |
| `app/Providers/ModuleServiceProvider.php` | ✅ Ada | Load migrations dari `app/Modules/*/Database/Migrations/` dan `app/Plugins/*/Database/Migrations/`, load routes & views per module. |
| `app/Providers/AuthServiceProvider.php` | ⚠️ Ada tapi kurang | Hanya daftar `$policies[]`, `boot()` kosong. **Tidak ada `Gate::before` untuk bypass super_admin.** |
| `app/Providers/PluginRegistryServiceProvider.php` | ✅ Ada (Epic 4) | Boot `PluginRegistry::syncToDatabase()`. |
| `app/Providers/EventServiceProvider.php` | ✅ Ada | Listens events untuk Kurikulum subscribers. |

### 3.4 Migrations — Tenancy (4) ✅

```
app/Modules/Tenancy/Database/Migrations/
├── 0001_01_01_000003_create_tenants_table.php         ✅
├── 0001_01_01_000004_create_branches_table.php        ✅ + add FK users.branch_id
├── 0001_01_01_000005_create_tenant_settings_table.php ✅
└── 0001_01_01_000006_create_subscriptions_table.php   ✅
```

**Verifikasi skema:** Semua sesuai design.md §3.2. PK BIGINT auto-increment, FK ke `tenants`/`users`, soft deletes, `created_by/updated_by` FK ke users.

### 3.5 Migrations — Auth/RBAC (9 di design, aktual 4 modular + 1 default) ⚠️

| File | Status | Catatan |
|---|---|---|
| `database/migrations/0001_01_01_000000_create_users_table.php` | ✅ | Skema users dengan tenant_id, branch_id, must_reset_password, userable morphTo, soft deletes. |
| `database/migrations/0001_01_01_100000_create_roles_and_permissions_table.php` | ✅ | 5 tabel Spatie (roles, permissions, role_has_permissions, model_has_roles, model_has_permissions) + `team_id` sesuai `config/permission.php teams=true`. |
| `database/migrations/0001_01_01_100001_create_login_logs_table.php` | ✅ | Bonus — tidak ada di spec Epic 1 tapi berguna. |
| `database/migrations/0001_01_01_100002_create_activity_logs_table.php` | ✅ | Bonus. |
| `app/Modules/Auth/Database/Migrations/2026_06_20_000020_create_audit_logs_table.php` | ✅ | JSON `properties`, `before`, `after`, index. Match design.md. |
| `app/Modules/Auth/Database/Migrations/2026_06_20_000030_create_menus_table.php` | ✅ | Sesuai RBAC 5-lapis design.md §4. |
| `app/Modules/Auth/Database/Migrations/2026_06_20_000040_create_fields_table.php` | ✅ | Field ACL level 3. |
| **`sessions` & `cache` & `jobs` migrations** | ✅ | `0001_01_01_000001_create_cache_table.php` & `0001_01_01_000002_create_jobs_table.php` ada. Sessions di Laravel 11 default via `php artisan session:table` — belum dipublish tapi `.env` set `SESSION_DRIVER=database` → **akan fail** saat app diakses tanpa `session:table`. ⚠️ |
| **`menu_role_overrides` & `field_role_overrides`** | ❌ MISSING | Design.md §3.2 menyebut tabel RBAC Menu ACL & Field ACL masing-masing 2 tabel (master + override). **Tidak ada migration** untuk `menu_role_overrides` dan `field_role_overrides`. Hanya tabel `menus` dan `fields` yang dibuat. |

### 3.6 Migrations — Plugin Infrastructure (2 di design, aktual 1) ⚠️

| File | Status |
|---|---|
| `app/Plugins/Infrastructure/Database/Migrations/2026_06_20_000050_create_plugins_table.php` | ✅ Ada |
| **`tenant_plugins` migration** | ❌ MISSING — design.md menyebut tabel `tenant_plugins` (pivot plugin-tenant) tapi **tidak ada migration-nya**. `PluginRegistry::isActiveForTenant()` sudah defensive-check `Schema::hasTable('tenant_plugins')` (returns false), jadi tidak crash — tapi **fitur aktivasi plugin per-tenant tidak berfungsi**. |

### 3.7 Seeders Epic 1 (4 wajib) ✅

| File | Status | Verifikasi Run |
|---|---|---|
| `database/seeders/SuperAdminSeeder.php` | ✅ | Seed `username=superadmin`, password `SuperAdmin#2026`, tenant_id NULL, role `super_admin`. |
| `database/seeders/RolePermissionSeeder.php` | ⚠️ | Seed 43 permissions + 10 roles. **Banyak permission yang dipakai `MenuSeeder` TIDAK ada di daftar** — lihat §4.2. |
| `database/seeders/MenuSeeder.php` | ⚠️ | Seed 18 menus, 17 di antaranya reference permission yang tidak terdaftar. |
| `database/seeders/FieldSeeder.php` | ⚠️ | Seed 10 fields, beberapa reference model class yang tidak ada (`TagihanSiswa`, `Pembayaran`, `TabunganSiswa`) — model aktual adalah `App\Modules\Finance\Models\TagihanSiswa` (with namespace) jadi kode akan rusak saat Field ACL diaktifkan. |

### 3.8 Tests Epic 1 (5 wajib)

| File | Status |
|---|---|
| `tests/Unit/Support/TenantContextTest.php` | ✅ 5 assertions pass |
| `tests/Unit/Models/Traits/BelongsToTenantTraitTest.php` | ✅ 3 assertions pass |
| `tests/Unit/Models/Traits/TracksAuditColumnsTest.php` | ✅ 1 assertion pass |
| `tests/Feature/Setup/DatabaseConnectionTest.php` | ❌ 2 fail (default & legacy) — butuh `pdo_mysql`, di env ini hanya `pdo_sqlite`. |
| `tests/Feature/Setup/ResolveTenantMiddlewareTest.php` | ✅ 2 assertions pass |
| `tests/Feature/AuthTest.php` (3 assertions) | ✅ pass |
| `tests/Feature/ExampleTest.php` | ✅ pass |
| `tests/Feature/ScheduleTest.php` | ✅ pass |

**Total:** 19 Epic 1-specific tests, 17 pass, 2 fail di env ini (env-only, bukan code).

### 3.9 User Model (Spatie teams + Impersonate) ✅

`app/Models/User.php` (192 baris):
- ✅ `use HasRoles, HasPermissions` dengan trait aliasing
- ✅ Override `assignRole`, `removeRole`, `syncRoles`, `hasRole`, `hasAnyRole`, `hasAllRoles`, `hasPermissionTo`, `givePermissionTo`, `revokePermissionTo`, `syncPermissions`
- ✅ `runInTeamContext()` wrapper dengan `try/finally` — match DEV_DOCS-013 §1
- ✅ `use Impersonate` (lab404)
- ✅ `isSuperAdmin()`, `canImpersonate()`, `canBeImpersonated()`
- ✅ Fillable: `tenant_id, branch_id, username, nama, email, tipe, password, foto, aktif, must_reset_password, last_login_at, userable_type, userable_id`

### 3.10 Config

| File | Status |
|---|---|
| `config/permission.php` | ✅ `'teams' => true`, table names standar Spatie, `team_foreign_key => 'team_id'`. Match ADR-006. |
| `config/database.php` | ✅ Ada koneksi `mysql` (default ke `sisfokol_laravel`) DAN `legacy_mysql` (read_only=true, default ke `sisfokolv7`). Match design.md. |
| `config/modules.php` | ✅ Core 6 module + plugins_path. |
| `config/laravel-impersonate.php` | ✅ Published. |

---

## 4. Gap Analysis Detail

### 4.1 GAP #1 (CRITICAL — BREAKING): `CurriculumController` Missing

**Lokasi:** `app/Modules/Evaluation/routes.php:5,24-26`  
**Manifestasi:** Setiap request HTTP ke app mana pun melempar `ReflectionException: Class "App\Modules\Evaluation\Controllers\CurriculumController" does not exist` saat route loading di ModuleServiceProvider::boot().

**Bukti fisik:**
```bash
$ ls app/Modules/Evaluation/Controllers/
GradeEntryController.php  RaporController.php
# TIDAK ADA CurriculumController.php

$ grep -n "CurriculumController" app/Modules/Evaluation/routes.php
5:use App\Modules\Evaluation\Controllers\CurriculumController;
24:        Route::get('/curriculum', [CurriculumController::class, 'index'])->name('curriculum.index');
25:        Route::get('/curriculum/create', [CurriculumController::class, 'create'])->name('curriculum.create');
26:        Route::post('/curriculum', [CurriculumController::class, 'store'])->name('curriculum.store');
```

**Patch sementara yang saya terapkan agar app bisa boot:** Comment-out 3 route dan `use` statement di `routes.php` (lihat diff di §6.1). Patch ini **TIDAK boleh masuk production** — harus implement controller asli.

**Root cause:** Sepertinya Epic 6 (Evaluation) menambahkan route tapi controller tidak ikut di-commit, atau file terhapus saat merge.

### 4.2 GAP #2 (CRITICAL — fitur rusak): Permission Mismatch MenuSeeder ↔ RolePermissionSeeder

**Lokasi:** `database/seeders/MenuSeeder.php` vs `database/seeders/RolePermissionSeeder.php`  
**Manifestasi:** Setelah seeding, 17 dari 18 menu memiliki `permission_required` yang **tidak terdaftar** di tabel `permissions`. MenuRenderer (di `app/Support/MenuRenderer.php`) akan check `hasPermissionTo()` → selalu return `false` → **tidak ada menu yang tampil** di sidebar untuk user apa pun (kecuali yang punya `plugin.activate`).

**Tabel Gap (dihasilkan dari audit DB):**

| Menu `kode` | Menu `permission_required` | Ada di `permissions` table? |
|---|---|---|
| `dashboard` | `dashboard.view` | ❌ TIDAK ADA |
| `tenancy.tenants` | `tenant.view` | ❌ TIDAK ADA |
| `tenancy.branches` | `tenant.view` | ❌ TIDAK ADA |
| `auth.users` | `user.manage` | ❌ TIDAK ADA (yang ada: `user.*` wildcard) |
| `auth.rbac` | `rbac.manage` | ❌ TIDAK ADA |
| `auth.audit` | `audit.view` | ❌ TIDAK ADA |
| `auth.plugins` | `plugin.activate` | ✅ ADA |
| `academic.siswa` | `siswa.view` | ❌ TIDAK ADA (yang ada: `student.view`) |
| `academic.guru` | `guru.view` | ❌ TIDAK ADA (yang ada: `employee.view`) |
| `academic.kelas` | `kelas.view` | ❌ TIDAK ADA |
| `academic.mapel` | `mapel.view` | ❌ TIDAK ADA (yang ada: `master.subject.*`) |
| `academic.jadwal` | `jadwal.view` | ❌ TIDAK ADA (yang ada: `academic.schedule.view`) |
| `finance.tagihan` | `tagihan.view` | ❌ TIDAK ADA (yang ada: `finance.student-bill.view`) |
| `finance.bayar` | `pembayaran.view` | ❌ TIDAK ADA (yang ada: `finance.student-payment.view`) |
| `finance.tabungan` | `tabungan.view` | ❌ TIDAK ADA (yang ada: `finance.student-saving.*`) |
| `presence.presensi` | `presensi.view` | ❌ TIDAK ADA (yang ada: `presence.view`) |
| `presence.absensi` | `absensi.view` | ✅ ADA |
| `evaluation.rapor` | `raport.view` | ❌ TIDAK ADA |

**Root cause:** Ada **inkonsistensi vocabulary** antara `RolePermissionSeeder` (menggunakan konvensi design.md: `student.view`, `academic.schedule.view`, `finance.student-bill.view`) vs `MenuSeeder` (menggunakan konvensi lama Indonesia: `siswa.view`, `jadwal.view`, `tagihan.view`). Kedua seeder ditulis oleh agent/fase berbeda tanpa sinkronisasi.

### 4.3 GAP #3 (CRITICAL — security): SuperAdmin Tidak Ada Bypass Global

**Lokasi:** `app/Providers/AuthServiceProvider.php`  
**Manifestasi:** `RolePermissionSeeder` membuat role `super_admin` tapi **tidak memberikan permission apa pun** (`'super_admin' => []` implisit, tidak ada di array `$roles`). `AuthServiceProvider::boot()` kosong — tidak ada `Gate::before(fn $user => $user->isSuperAdmin())` untuk bypass global.

**Akibat:** SuperAdmin yang login tidak akan bisa mengakses route yang dipasang middleware `permission:xxx` (mis. `Route::middleware('permission:audit.view')`). Hanya route yang menggunakan `role:super_admin` (langsung) yang bisa diakses.

**Bukti dari DB setelah seeding:**
```
role: super_admin, permissions: 0
role: admin,        permissions: 43 (semua)
```

**Patch yang direkomendasikan:** Tambahkan di `AuthServiceProvider::boot()`:
```php
Gate::before(function (User $user) {
    if ($user->isSuperAdmin()) return true;
});
```

### 4.4 GAP #4 (STRUCTURAL — Confusing): Skema Duplikat

Design.md §3.2 menetapkan 48 tabel modular (Bahasa Indonesia: `siswa`, `guru`, `jadwal`, `tagihan_siswa`, `pembayaran`, `tabungan_siswa`, `presensi`, `absensi`, `izin`, …).  
Aktual: terdapat **2 set skema paralel**:

| Sumber Migration | Konvensi | Jumlah Tabel | Dipakai oleh |
|---|---|---|---|
| `database/migrations/` (legacy Laravel default) | English plural (`students`, `employees`, `schedules`, `student_bills`, `student_payments`, `attendances`, `absences`, `permits`, …) | 66 tabel | `app/Models/*` + `app/Http/Controllers/Admin/*`, `Teacher/*`, `Finance/*`, `Picket/*`, `Counselor/*` (di `routes/web.php`) |
| `app/Modules/*/Database/Migrations/` (modular design.md) | Indonesia singular (`siswa`, `guru`, `jadwal`, `tagihan_siswa`, `pembayaran`, `tabungan_siswa`, `presensi`, `absensi`, `izin`, …) | 26 tabel | `app/Modules/*/Models/*` + `app/Modules/*/Controllers/*` (di `app/Modules/*/routes.php`) |

**Akibat:**
- Total tabel di DB: **107** (bukan 48 sesuai design).
- Aplikasi punya **dua source of truth** untuk entitas yang sama. Mis. data siswa bisa diakses via `Student` (table `students`) atau via `Siswa` (table `siswa`).
- `app/Http/Controllers/Admin/SubjectController.php` (legacy) vs `app/Modules/Academic/Models/Mapel.php` (modular) — keduanya handle mata pelajaran dengan tabel berbeda.
- ETL pipeline (Epic 11) akan bingung: target write ke `siswa` atau `students`?

**Root cause:** Implementasi Epic 1-9 berjalan paralel dengan iterasi design. Modul-modul awal (sebelum commit `21b9d87 initial upload`) menggunakan konvensi legacy Laravel. Modul-modul yang ditulis ulang di `app/Modules/` mengikuti design.md final. Tidak ada fase **konsolidasi**.

### 4.5 GAP #5 (ENVIRONMENT): `.env.example` Tidak Sesuai Design

| Variabel | `.env.example` | Design.md / DEV_DOCS |
|---|---|---|
| `DB_CONNECTION` | `sqlite` ❌ | `mysql` ✅ |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Di-comment ❌ | Harus aktif dengan `127.0.0.1:3306` + `sisfokol_laravel` |
| `LEGACY_DB_*` | TIDAK ADA ❌ | Harus ada block `LEGACY_DB_*` untuk koneksi read-only ke `sisfokolv7` |
| `SESSION_DRIVER` | `database` ⚠️ | OK asalkan `php artisan session:table` sudah dijalankan |
| `CACHE_STORE` | `database` ⚠️ | OK asalkan `cache` migration sudah dijalankan (sudah ada) |

**Akibat:**
1. `composer install && cp .env.example .env && php artisan key:generate && php artisan migrate` di fresh checkout → DB pakai SQLite. Tabel `tenants` FK ke `users` akan jalan, tapi **`tests/Feature/Setup/DatabaseConnectionTest::it_can_connect_to_default_database`** yang hardcode ke koneksi `mysql` akan gagal.
2. `it_can_connect_to_legacy_database` selalu gagal karena koneksi `legacy_mysql` butuh server MySQL/MariaDB eksternal.
3. Developer baru yang follow DEV_DOCS-012 akan bingung karena instruksi menyebut `sisfokol_laravel` MySQL, tapi `.env.example` pakai SQLite.

### 4.6 GAP #6 (MINOR): FieldSeeder Model Reference Tidak Match

`database/seeders/FieldSeeder.php` mengisi kolom `model` dengan:
- `'Siswa'` (match `App\Modules\Academic\Models\Siswa`) ✅
- `'OrangTua'` (match) ✅
- `'TagihanSiswa'` ❌ — model aktual `App\Modules\Finance\Models\TagihanSiswa`
- `'Pembayaran'` ❌ — model aktual `App\Modules\Finance\Models\Pembayaran`
- `'TabunganSiswa'` ❌ — model aktual `App\Modules\Finance\Models\TabunganSiswa`

Saat FieldAcl evaluator resolve model by name (string), 3 field terakhir tidak akan ketemu.

### 4.7 GAP #7 (TEST): `QrScanTest::test_qr_scan_prevents_duplicate_scans_on_same_day` Fail

```
1) Tests\Feature\Presence\QrScanTest::test_qr_scan_prevents_duplicate_scans_on_same_day
Failed asserting that exception of type "Exception" is thrown.
```

**Lokasi masalah:** `app/Modules/Presence/Services/QrScannerService.php:64-67`
```php
$exists = Attendance::where('user_id', $user->id)
    ->where('date', $dateStr)
    ->where('type', $type)
    ->exists();
```

**Hipotesis:** Model `Attendance` di `app/Models/Attendance.php` menggunakan salah satu trait (`BelongsToTenant`) yang menambah global scope `tenant_id`. Saat scan kedua di test, TenantContext mungkin tidak konsisten sehingga query `Attendance::where(...)` tidak menemukan record yang baru dibuat. Atau: kolom `date` di tabel `attendances` ter-shadow oleh kolom lain (mis. `attendance_date`), atau nama kolom bukan `date` setelah alter migration `2026_06_21_000100_alter_attendances_table.php`.

**Investigasi lebih lanjut diperlukan** — jalankan test isolated dan dump query log.

### 4.8 GAP #8 (GIT HISTORY): Tidak Ada Commit per-Epic

```
$ git log --oneline | wc -l
88 commits

$ git log --oneline -- sisfokol-laravel/app/Modules/Tenancy
21b9d87 initial upload    ← hanya 1 commit, sisanya merge/docs
```

- **`dd6bfa3 initials`** (2026-06-20) — initial README/docs only.
- **`21b9d87 initial upload`** (2026-06-20) — **bulk upload 403 file** berisi seluruh implementasi Epic 1-9 dalam SATU commit. Tidak ada isolasi commit per-Epic.
- Commit setelahnya: tambah DemoSeeder, Kurikulum plugin, dll per fitur kecil.

**Akibat:**
- Tidak bisa `git bisect` untuk menemukan kapan bug diperkenalkan.
- Tidak bisa `git revert` per-Epic.
- `git blame` pada file Epic 1 (mis. `TenantContext.php`) hanya menunjuk ke `21b9d87` — tidak informatif.
- Klaim DEV_DOCS-013 "Epic 1 selesai" tidak bisa diverifikasi via git history (semua jadi di satu commit).

### 4.9 GAP #9 (MINOR): Telescope Auto-Loaded Tapi Migrations Tidak Auto-Publish

`composer.json` require-dev: `"laravel/telescope": "^5.0"`. Telescope otomatis listen semua query. Tanpa `php artisan telescope:install`, tabel `telescope_entries` tidak ada → setiap command artisan melempar exception saat `terminate()` mencoba insert.

**Sudah saya publish saat audit**, tapi harus didokumentasikan sebagai langkah setup wajib atau Telescope harus didisable di `.env` (`TELESCOPE_ENABLED=false`).

---

## 5. Bukti Aplikasi Berjalan (Run Result)

### 5.1 Setup yang Berhasil

```bash
# PHP 8.4 dari Debian Trixie .deb (tidak butuh sudo)
$ php -v  →  PHP 8.4.21 (cli)
$ php -m  →  bcmath, calendar, ctype, curl, dom, exif, fileinfo, filter,
             gd, hash, iconv, intl, json, libxml, mbstring, openssl,
             PDO, pdo_sqlite, Phar, posix, readline, SimpleXML, sockets,
             sodium, sqlite3, tokenizer, xml, xmlreader, xmlwriter, xsl,
             zip, Zend OPcache, zlib

# Composer
$ composer --version  →  Composer version 2.10.1

# Install dependencies
$ composer install --no-interaction --no-scripts --prefer-dist
  → 132 packages installed sukses (setelah aktifkan ext-gd dan ext-zip)

# Setup env
$ cp .env.example .env
$ sed -i 's/^DB_CONNECTION=sqlite/DB_CONNECTION=sqlite/' .env   # tetap sqlite karena no MySQL
$ echo "TELESCOPE_ENABLED=false" >> .env
$ php artisan key:generate
  → INFO Application key set successfully.

# Run migrations
$ php artisan migrate --force
  → 79 migrations DONE (terhitung 0.5ms - 67ms per migration)

# Run seeders
$ php artisan db:seed --force
  → 14 seeder DONE
  → Output DemoSeeder:
    +----------------+----------------+-----------------+
    | Role           | Username       | Password        |
    +----------------+----------------+-----------------+
    | SuperAdmin     | superadmin     | SuperAdmin#2026 |
    | Admin Sekolah  | admin          | password        |
    | Admin (Tenant) | admin.sekolah  | demo1234        |
    | Guru Piket     | piket.demo     | demo1234        |
    | Guru BK        | bk.demo        | demo1234        |
    | Guru Mapel     | guru.demo      | demo1234        |
    | Wali Kelas     | walikelas.demo | demo1234        |
    | Siswa (contoh) | siswa.2024001  | demo1234        |
    +----------------+----------------+-----------------+
```

### 5.2 Hasil Test Suite (115 tests)

```
PHPUnit 11.5.55 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.4.21
Configuration: /home/z/my-project/sisfokolv7/sisfokol-laravel/phpunit.xml

...............................................................  63 / 115 ( 54%)
..................................F.............EE..            115 / 115 (100%)

Time: 00:09.693, Memory: 28.00 MB

Tests: 115, Assertions: 286, Errors: 2, Failures: 1.
```

| Status | Test | Penyebab |
|---|---|---|
| ❌ ERROR | `Tests\Feature\Setup\DatabaseConnectionTest::it_can_connect_to_default_database` | `pdo_mysql` tidak tersedia di env audit; test hardcode koneksi `mysql`. Bukan code bug. |
| ❌ ERROR | `Tests\Feature\Setup\DatabaseConnectionTest::it_can_connect_to_legacy_database` | Sama, koneksi `legacy_mysql`. Bukan code bug. |
| ❌ FAIL | `Tests\Feature\Presence\QrScanTest::test_qr_scan_prevents_duplicate_scans_on_same_day` | **Code bug asli**. Lihat §4.7. |
| ✅ 112 PASS | lainnya | Termasuk semua Epic 1 unit tests + feature tests Epic 2-9. |

### 5.3 HTTP Server Run

**Percobaan 1: `php artisan serve`** → FAIL. Error: `ReflectionException: Class "App\Modules\Evaluation\Controllers\CurriculumController" does not exist`. Server jalan di port 8000 tapi semua request error 500.

**Percobaan 2 (setelah comment route broken):** `php artisan serve` → server jalan, `/login` render 200, **tapi POST `/login` rate-limited (429)** karena `throttle:5,1` di route.

**Percobaan 3 (direct kernel test):** Menggunakan script PHP yang langsung memanggil `Illuminate\Contracts\Http\Kernel::handle()`:

```php
$request = Request::create('/login', 'GET');
$response = $kernel->handle($request);
// Status: 200, Content length: 8002
// Contains 'Login — SISFOKOL': YES
```

Auth flow via `Auth::attempt`:
```
Auth::attempt(['username'=>'superadmin','password'=>'SuperAdmin#2026']) → SUCCESS
  Authenticated as: superadmin (id=1)
  isSuperAdmin(): YES
  hasRole('super_admin'): YES
  password 'SuperAdmin#2026' valid: YES

Auth::attempt(['username'=>'admin.sekolah','password'=>'demo1234']) → SUCCESS
  Authenticated as: admin.sekolah (id=3, tenant_id=1)
  hasRole('admin'): YES
```

### 5.4 Snapshot Login Page (HTML)

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="GGvDpil3H4yJ3I0Ew8kzZPFw40CDfgbI5ADQeK2h">
    <title>Login — SISFOKOL Laravel</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    ...
</head>
```

✅ Halaman login SISFOKOL Laravel render sempurna dengan Bootstrap 5, Font Awesome, Google Fonts Outfit, dark gradient background.

---

## 6. Rekomendasi Perbaikan Bertahap

### 6.1 Tahap 1 — Quick Fix (Hari 1, ≤4 jam) — Agar App Bisa Dipresentasikan

#### Fix 1.1: Implement `CurriculumController` (atau comment permanen)

**Opsi A (quick):** Comment out route + use di `app/Modules/Evaluation/routes.php` — sudah saya lakukan di workspace ini, tapi belum di-commit. **JANGAN dipakai production.**

**Opsi B (proper):** Buat file `app/Modules/Evaluation/Controllers/CurriculumController.php` dengan implementasi CRUD minimal untuk CP (Capaian Pembelajaran) & Materi Ajar. Kurang lebih:

```php
<?php
namespace App\Modules\Evaluation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CurriculumCompetency;
use App\Models\CurriculumLearningMaterial;
use App\Modules\Academic\Models\Mapel;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function index(Request $request)
    {
        $mapelId = $request->get('mapel_id');
        $competencies = CurriculumCompetency::when($mapelId, fn($q) => $q->where('mapel_id', $mapelId))->get();
        $mapels = Mapel::all();
        return view('evaluation.curriculum.index', compact('competencies', 'mapels', 'mapelId'));
    }

    public function create()
    {
        $mapels = Mapel::all();
        return view('evaluation.curriculum.create', compact('mapels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mapel_id' => 'required|exists:mapel,id',
            'kode' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'jenis' => 'required|in:TP,LM',
        ]);
        CurriculumCompetency::create($data);
        return redirect()->route('evaluation.curriculum.index')->with('success', 'CP/LM berhasil ditambahkan.');
    }
}
```

#### Fix 1.2: Sinkronisasi Permission & Menu Vocabulary

**Edit `database/seeders/RolePermissionSeeder.php`** — tambahkan permission yang dipakai MenuSeeder:

```php
$permissions = [
    // Master (existing)
    'master.school-profile.view', 'master.school-profile.update',
    // ... (yang sudah ada)

    // TAMBAHAN untuk MenuSeeder (yang missing):
    'dashboard.view',
    'tenant.view', 'tenant.manage',
    'user.manage', 'rbac.manage', 'audit.view',
    'siswa.view', 'siswa.create', 'siswa.update', 'siswa.delete',
    'guru.view', 'guru.create', 'guru.update', 'guru.delete',
    'kelas.view', 'kelas.create', 'kelas.update', 'kelas.delete',
    'mapel.view', 'mapel.create', 'mapel.update', 'mapel.delete',
    'jadwal.view', 'jadwal.create', 'jadwal.update', 'jadwal.delete',
    'tagihan.view', 'tagihan.create', 'tagihan.update', 'tagihan.delete',
    'pembayaran.view', 'pembayaran.create', 'pembayaran.update',
    'tabungan.view', 'tabungan.create', 'tabungan.update',
    'presensi.view', 'presensi.create',
    'raport.view', 'raport.cetak',
];
```

**ATAU** edit `database/seeders/MenuSeeder.php` agar `permission_required` match dengan yang sudah ada di RolePermissionSeeder (mis. ganti `siswa.view` → `student.view`, `jadwal.view` → `academic.schedule.view`, dst).

**Rekomendasi:** Pilih Opsi A (tambah permission) karena konvensi `<resource>.<aksi>` Indonesia (`siswa.view`) lebih natural untuk codebase SISFOKOL yang domain-nya Indonesia. Setelah itu, perbarui `role_has_permissions` agar role `teacher` punya `siswa.view`, dst.

#### Fix 1.3: Tambah Gate::before untuk SuperAdmin

**Edit `app/Providers/AuthServiceProvider.php`:**

```php
public function boot(): void
{
    Gate::before(function (User $user) {
        if ($user->isSuperAdmin()) return true;
    });
}
```

Dengan ini, super_admin tidak perlu diberi 43 permission eksplisit; cukup rely on `Gate::before`.

#### Fix 1.4: Publish Session Table & Telescope Migration

```bash
php artisan session:table
php artisan telescope:install    # jika belum
php artisan migrate
```

Atau tambahkan langkah ini ke `README.md` dan `DEV_DOCS` setup.

#### Fix 1.5: Update `.env.example`

```env
APP_NAME="SISFOKOL v7"
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sisfokol_laravel
DB_USERNAME=root
DB_PASSWORD=

LEGACY_DB_HOST=127.0.0.1
LEGACY_DB_PORT=3306
LEGACY_DB_DATABASE=sisfokolv7
LEGACY_DB_USERNAME=root
LEGACY_DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

TELESCOPE_ENABLED=false
```

### 6.2 Tahap 2 — Mid-Term (Minggu 1, 3-5 hari) — Konsolidasi Arsitektur

#### Fix 2.1: Pilih Satu Set Skema (Modular Indonesia) dan Drop Legacy

**Goal:** Eliminasi dual-schema confusion. Target: 48 tabel sesuai design.md.

**Langkah:**
1. Audit semua controller di `app/Http/Controllers/Admin/*`, `Teacher/*`, `Finance/*`, `Picket/*`, `Counselor/*` — apakah mereka di-route via `routes/web.php`?
2. Migrasikan logic ke controller modular di `app/Modules/*/Controllers/` (yang sudah ada untuk Academic, Auth, Finance, Presence, Evaluation).
3. Hapus file controller legacy yang sudah tidak terpakai.
4. Hapus `database/migrations/0001_01_01_200022_create_students_table.php`, dst. (semua migration English plural yang punya kembaran modular).
5. Update `routes/web.php` agar hanya merujuk controller modular.
6. Drop tabel legacy dari DB: `php artisan db:statement('DROP TABLE students');` dst.

**Estimasi effort:** 3-5 hari untuk 1 developer yang sudah paham codebase.

#### Fix 2.2: Implement Migrations yang Missing

- `app/Modules/Auth/Database/Migrations/2026_06_20_000035_create_menu_role_overrides_table.php`
- `app/Modules/Auth/Database/Migrations/2026_06_20_000045_create_field_role_overrides_table.php`
- `app/Plugins/Infrastructure/Database/Migrations/2026_06_20_000051_create_tenant_plugins_table.php`

Skema sesuai design.md §4 RBAC 5-lapis.

#### Fix 2.3: Fix FieldSeeder Model Namespace

**Edit `database/seeders/FieldSeeder.php`:**
```php
['kode' => 'tagihan.nominal_kurang', 'model' => 'App\Modules\Finance\Models\TagihanSiswa', ...],
['kode' => 'pembayaran.total',        'model' => 'App\Modules\Finance\Models\Pembayaran', ...],
['kode' => 'tabungan.saldo',          'model' => 'App\Modules\Finance\Models\TabunganSiswa', ...],
```

Pastikan `FieldAcl::resolveModel()` menerima FQCN, bukan short name.

#### Fix 2.4: Investigasi & Fix QrScanTest Failure

Buka `app/Models/Attendance.php`:
- Cek apakah ada trait `BelongsToTenant` yang menambah global scope.
- Cek nama kolom `date` setelah alter migration `2026_06_21_000100_alter_attendances_table.php`.
- Tambahkan log `DB::enableQueryLog()` di `QrScannerService::scan()` untuk melihat query sebenarnya.

#### Fix 2.5: Isolasi Commit per-Epic (Git Hygiene)

Untuk Epics berikutnya:
1. Buat branch `epic-N-...` untuk setiap Epic.
2. Commit per-task dalam Epic (bukan satu bulk commit).
3. PR per-Epic, jangan gabung.

Untuk historis: tidak bisa di-retrofit tanpa `git rebase` destruktif. Cukup catat di DEV_DOCS bahwa commit `21b9d87` adalah bulk dan tidak bisa di-bisect.

### 6.3 Tahap 3 — Long-Term (Minggu 2+, 1-2 minggu) — Production Readiness

#### Fix 3.1: Setup MySQL/MariaDB untuk Production

- Install MySQL 8 atau MariaDB 11.
- Buat DB `sisfokol_laravel` (InnoDB, `utf8mb4_unicode_ci`).
- Import DB legacy `sisfokol_v7` (read-only, untuk ETL Epic 11).
- Update `.env` (bukan `.env.example`) dengan kredensial MySQL.
- Re-run `php artisan migrate:fresh --seed` di MySQL.
- Re-run `php artisan test` — `DatabaseConnectionTest` harus pass.

#### Fix 3.2: Tambah Setup Wizard / README.md

Update `README.md` di root project dengan langkah:
```bash
# Prerequisites
PHP 8.3+ (or 8.4)
MySQL 8 / MariaDB 11
Composer 2.x
Node 18+ (untuk Vite asset building)

# Setup
git clone https://github.com/haisyamalawwab/sisfokolv7.git
cd sisfokolv7/sisfokol-laravel
cp .env.example .env
# Edit .env: set DB credentials
composer install --no-interaction --prefer-dist
php artisan key:generate
php artisan session:table
php artisan telescope:install
php artisan migrate:fresh --seed
php artisan serve
# Open http://localhost:8000/login
# Login: superadmin / SuperAdmin#2026
```

#### Fix 3.3: Tambah CI/CD Pipeline

`.github/workflows/test.yml`:
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: sisfokol_laravel
        ports: ['3306:3306']
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, mysql, gd, zip, intl, bcmath
      - run: composer install --no-interaction --prefer-dist
      - run: cp .env.example .env
      - run: php artisan key:generate
      - run: php artisan migrate:fresh --seed --force
      - run: php artisan test
```

#### Fix 3.4: Implement `audit.view` Permission Enforcement

`audit_logs` route sudah pakai `permission:audit.view` (di-imply oleh RBAC UI), tapi SuperAdmin butuh bypass (Fix 1.3) dan role `admin` perlu eksplisit diberi `audit.view`.

#### Fix 3.5: Implement Plugin Activation Flow

Setelah `tenant_plugins` table dibuat (Fix 2.2), implement:
- UI di `rbac.plugins.index` untuk toggle plugin per-tenant
- `PluginActivationService::activate($tenantId, $kode)` & `::deactivate()`
- Cache invalidation via `PluginRegistry::clearTenantCache()`

---

## 7. Verifikasi Bukti Implementasi (Checklist Epic 1)

Berikut checklist definitif untuk memverifikasi setiap claim DEV_DOCS-013:

| Claim DEV_DOCS-013 | Verifikasi Fisik | Status |
|---|---|---|
| "Spatie teams mode dengan wrapper `runInTeamContext`" | `app/Models/User.php:118-181` — method `runInTeamContext()` dengan `try/finally` + override 10 method Spatie | ✅ VERIFIED |
| "Menambahkan permission pada Seeder (10 permission baru)" | Cek diff `RolePermissionSeeder.php` — ada 43 permission. Apakah 10 permission yang dimaksud ada? `academic.schedule.view`, `academic.teacher-agenda.*`, `academic.curriculum.*`, `user.view`, `employee.view`, `student.view`, `absence.view` — semua ada. | ✅ VERIFIED |
| "Mendaftarkan alias middleware Spatie di `bootstrap/app.php`" | `bootstrap/app.php:19-27` — alias `role`, `permission`, `role_or_permission` terdaftar | ✅ VERIFIED |
| "ExampleTest assert target ubah dari `/` ke `/login`" | `tests/Feature/ExampleTest.php` — assert `/login` returns 200 | ✅ VERIFIED |
| "ScheduleTest invoke `RolePermissionSeeder` sebelum assign role" | `tests/Feature/ScheduleTest.php` — `$this->seed(RolePermissionSeeder::class)` di setUp | ✅ VERIFIED |
| "Seeding `php artisan db:seed` → 9 seeder DONE" | Aktual: **14 seeder DONE** (lebih banyak dari yang didokumentasikan) | ✅ VERIFIED+ |
| "Pengujian `php artisan test` → 19 tests pass (32 assertions)" | Aktual: **115 tests**, 286 assertions, 112 pass. 19 Epic 1-specific tests semua pass (kecuali 2 DatabaseConnectionTest yang butuh MySQL). | ⚠️ VERIFIED but env-limited |
| "Git tag `epic-1-setup`" | `git tag -l` → **TIDAK ADA tag** | ❌ NOT VERIFIED |

---

## 8. Kesimpulan

### 8.1 Apakah Epic 1 Benar-benar Selesai?

**Setengah ya, setengah tidak.**

- **Ya** untuk fondasi inti: trait, middleware, helper, seeder, migration tenancy+RBAC+ACL+plugin infra, User model override, config, provider, test Setup — semua ada dan berfungsi.
- **Tidak** untuk kelengkapan produksi:
  - Aplikasi **TIDAK bisa dijalankan end-to-end** tanpa patch (CurriculumController missing).
  - **Menu navigasi tidak tampil** untuk user mana pun karena permission mismatch.
  - **SuperAdmin tidak bisa akses** route ber-permission tanpa Gate::before.
  - **Skema duplikat** antara legacy & modular menyebabkan kebingungan data.
  - **Environment setup** tidak match antara `.env.example` dan design doc.

### 8.2 Apakah Ada Halusinasi di DEV_DOCS?

**Tidak ada halusinasi murni**, tetapi ada **over-claiming**:
- "100% Green" → sebenarnya 112/115 (97.4%) di env SQLite, dan DatabaseConnectionTest pasti fail di env MySQL default sebelum setup legacy DB.
- "19 tests pass" → 17 Epic 1-specific tests pass di SQLite, 2 fail karena butuh MySQL.
- "Epic 1 SELESAI" → fondasi selesai, tapi produksi belum siap.

### 8.3 Rekomendasi Prioritas

1. **MINGGU INI:** Patch Tahap 1 (Fix 1.1-1.5) agar aplikasi minimal bisa demo.
2. **MINGGU DEPAN:** Mulai Tahap 2 (konsolidasi skema + fix FieldSeeder + investigate QrScanTest).
3. **MINGGU 3-4:** Tahap 3 (MySQL setup, CI/CD, plugin activation flow).

### 8.4 Status Workspace Audit

- ✅ Repo berhasil di-clone ke `/home/z/my-project/sisfokolv7/`
- ✅ PHP 8.4 + Composer terpasang di `/home/z/php-root/` (tanpa sudo)
- ✅ `composer install` sukses (132 packages)
- ✅ `php artisan migrate` sukses (79 migrations, 107 tabel)
- ✅ `php artisan db:seed` sukses (27 users, 1 tenant, 10 roles)
- ✅ `php artisan test` dijalankan (112 pass / 2 env-error / 1 code-bug)
- ✅ Server HTTP dijalankan, login page render 200
- ✅ Auth flow superadmin & admin.sekolah diverifikasi sukses
- ⚠️ Patch sementara `CurriculumController` route di-comment agar app bisa boot — **perlu implementasi proper**

---

## 9. Lampiran

### 9.1 File yang Dimodifikasi di Workspace Audit

| File | Modifikasi | Tujuan |
|---|---|---|
| `app/Modules/Evaluation/routes.php` | Comment-out `CurriculumController` use + 3 route | Agar app bisa boot |
| `serve.php` (new) | Custom router untuk PHP built-in server | Bypass `php artisan serve` worker issue |
| `.env` (new) | Copy dari `.env.example` + APP_KEY + TELESCOPE_ENABLED=false | Run configuration |
| `database/database.sqlite` (new) | SQLite DB | Default DB untuk audit (MySQL tidak tersedia tanpa sudo) |
| `database/migrations/2026_06_22_073316_create_telescope_entries_table.php` (new) | Telescope migration | Publish via `php artisan telescope:install` |

### 9.2 Command Reproduksi Audit

```bash
# Setup PHP (di environment tanpa sudo)
mkdir -p /tmp/php-debs && cd /tmp/php-debs
apt-get download php8.4-cli php8.4-common php8.4-mysql php8.4-xml php8.4-curl \
  php8.4-mbstring php8.4-zip php8.4-gd php8.4-bcmath php8.4-sqlite3 php8.4-readline \
  php8.4-opcache php8.4-intl libzip5
mkdir -p /home/z/php-root
for deb in *.deb; do dpkg-deb -x "$deb" /home/z/php-root/; done

# Buat php.ini
cat > /home/z/php-root/etc/php/8.4/cli/php.ini <<'EOF'
[PHP]
extension_dir = "/home/z/php-root/usr/lib/php/20240924"
memory_limit = 512M
display_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_WARNING
date.timezone = Asia/Jakarta
extension=bcmath
extension=calendar
extension=ctype
extension=curl
extension=dom
extension=exif
extension=fileinfo
extension=gd
extension=gettext
extension=iconv
extension=intl
extension=mbstring
extension=pdo
extension=pdo_sqlite
extension=phar
extension=posix
extension=readline
extension=simplexml
extension=sockets
extension=sqlite3
extension=tokenizer
extension=xml
extension=xmlreader
extension=xmlwriter
extension=xsl
extension=zip
zend_extension=opcache
EOF

# Buat wrapper
mkdir -p /home/z/php-root/bin
cat > /home/z/php-root/bin/php <<'EOF'
#!/bin/bash
export LD_LIBRARY_PATH=/home/z/php-root/usr/lib/x86_64-linux-gnu:/home/z/php-root/usr/lib:${LD_LIBRARY_PATH:-}
exec /home/z/php-root/usr/bin/php -c /home/z/php-root/etc/php/8.4/cli/php.ini "$@"
EOF
chmod +x /home/z/php-root/bin/php

# Install composer
curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
php /tmp/composer-setup.php --install-dir=/home/z/php-root/usr/bin --filename=composer
cat > /home/z/php-root/bin/composer <<'EOF'
#!/bin/bash
export LD_LIBRARY_PATH=/home/z/php-root/usr/lib/x86_64-linux-gnu:/home/z/php-root/usr/lib:${LD_LIBRARY_PATH:-}
exec /home/z/php-root/usr/bin/php -c /home/z/php-root/etc/php/8.4/cli/php.ini /home/z/php-root/usr/bin/composer "$@"
EOF
chmod +x /home/z/php-root/bin/composer

# Add to PATH
export PATH="/home/z/php-root/bin:$PATH"

# Clone & setup
git clone https://github.com/haisyamalawwab/sisfokolv7.git /home/z/my-project/sisfokolv7
cd /home/z/my-project/sisfokolv7/sisfokol-laravel
cp .env.example .env
sed -i 's/^APP_NAME=Laravel/APP_NAME="SISFOKOL v7"/' .env
sed -i 's/^APP_TIMEZONE=UTC/APP_TIMEZONE=Asia\/Jakarta/' .env
sed -i 's/^APP_LOCALE=en/APP_LOCALE=id/' .env
echo "TELESCOPE_ENABLED=false" >> .env

# Install & run
composer install --no-interaction --no-scripts --prefer-dist
php artisan key:generate --force
touch database/database.sqlite
php artisan telescope:install --no-interaction
php artisan migrate:fresh --seed --force
php artisan test
```

### 9.3 Daftar File Epic 1 yang Terverifikasi Ada

```
app/Support/TenantContext.php                                    (49 lines)
app/Support/helpers.php                                          (142 lines)
app/Support/PluginContract.php                                   (32 lines)
app/Support/PluginRegistry.php                                   (111 lines)
app/Support/PluginContext.php                                    (28 lines)
app/Support/FieldAcl.php                                         (90 lines)
app/Support/MenuRenderer.php                                     (88 lines)
app/Support/BladeDirectives.php                                  (32 lines)
app/Models/Traits/BelongsToTenant.php                            (32 lines)
app/Models/Traits/TracksAuditColumns.php                         (29 lines)
app/Models/User.php                                              (192 lines)
app/Http/Middleware/ResolveTenant.php                            (37 lines)
app/Http/Middleware/ForcePasswordReset.php                       (24 lines)
app/Http/Middleware/BlockWhileImpersonating.php                  (28 lines)
app/Http/Middleware/EnsurePluginEnabled.php                      (28 lines)
app/Providers/AppServiceProvider.php                             (72 lines)
app/Providers/ModuleServiceProvider.php                          (79 lines)
app/Providers/AuthServiceProvider.php                            (62 lines)
app/Providers/EventServiceProvider.php                           (28 lines)
app/Providers/PluginRegistryServiceProvider.php                  (22 lines)
bootstrap/app.php                                                (31 lines)
config/permission.php                                            (40 lines)
config/database.php                                              (194 lines)
config/modules.php                                               (13 lines)
config/laravel-impersonate.php                                   (published)
database/seeders/DatabaseSeeder.php                              (28 lines)
database/seeders/SuperAdminSeeder.php                            (31 lines)
database/seeders/RolePermissionSeeder.php                        (150 lines)
database/seeders/MenuSeeder.php                                  (43 lines)
database/seeders/FieldSeeder.php                                 (29 lines)
database/seeders/DemoSeeder.php                                  (existing)
database/seeders/SchoolProfileSeeder.php                         (existing)
database/seeders/AcademicYearSeeder.php                          (existing)
database/seeders/DaySeeder.php                                   (existing)
database/seeders/HourSeeder.php                                  (existing)
database/seeders/TimeSlotSeeder.php                              (existing)
database/seeders/SubjectTypeSeeder.php                           (existing)
database/seeders/AttendanceTimeSeeder.php                        (existing)
database/seeders/UserSeeder.php                                  (existing)
database/seeders/ClassroomSeeder.php                             (existing)
app/Modules/Tenancy/Database/Migrations/
  0001_01_01_000003_create_tenants_table.php
  0001_01_01_000004_create_branches_table.php
  0001_01_01_000005_create_tenant_settings_table.php
  0001_01_01_000006_create_subscriptions_table.php
app/Modules/Auth/Database/Migrations/
  2026_06_20_000020_create_audit_logs_table.php
  2026_06_20_000030_create_menus_table.php
  2026_06_20_000040_create_fields_table.php
app/Plugins/Infrastructure/Database/Migrations/
  2026_06_20_000050_create_plugins_table.php
tests/Unit/Support/TenantContextTest.php
tests/Unit/Models/Traits/BelongsToTenantTraitTest.php
tests/Unit/Models/Traits/TracksAuditColumnsTest.php
tests/Feature/Setup/DatabaseConnectionTest.php
tests/Feature/Setup/ResolveTenantMiddlewareTest.php
tests/Feature/AuthTest.php
tests/Feature/ExampleTest.php
tests/Feature/ScheduleTest.php
```

---

**Akhir Laporan.**  
*Dibuat oleh Super Z (Z.ai) — 2026-06-22.*
