# Dev Report: Audit Epic 5 — Academic Module

**Tanggal**: 2026-06-25  
**Tipe**: Audit / Verifikasi  
**Scope**: Epic 5 — Academic Module (Akademik Sekolah)  
**Status**: PARTIALLY IMPLEMENTED (~30% of full CRUD scope)  
**Auditor**: ZCode Agent (automated code-level verification + test run)

---

## 1. Ringkasan Eksekutif

Walkthrough sebelumnya (DEV_DOCS-026) mengklaim **"COMPLETED and VERIFIED"**.  
Hasil audit ini menunjukkan klaim tersebut **tidak akurat**.

**Data layer** (models, migrations, services) solid dan tested.  
**Presentation layer** (controllers, views, routes) hanya dibangun untuk **Siswa** — 9 entitas lainnya tidak memiliki controller, views, atau routes.

---

## 2. Test Run — Semua Pass

```
PASS  Tests\Feature\Academic\JadwalConflictTest          (3 tests)
PASS  Tests\Feature\Academic\KelasSiswaPromotionTest     (2 tests)
PASS  Tests\Feature\Academic\SiswaCrudTest               (6 tests)
PASS  Tests\Feature\Academic\TenantIsolationTest          (1 test)
─────────────────────────────────────────────────────────
Tests: 12 passed (62 assertions)   Duration: 72.02s
```

---

## 3. Komponen yang SUDAH Diimplementasi (Verified)

### 3.1 Models — 11/11 ✅

| Model | File | Traits | SoftDeletes |
|-------|------|--------|-------------|
| Siswa | `Modules/Academic/Models/Siswa.php` | BelongsToTenant, TracksAuditColumns | ✅ |
| Guru | `Modules/Academic/Models/Guru.php` | BelongsToTenant, TracksAuditColumns | ✅ (model) |
| Kelas | `Modules/Academic/Models/Kelas.php` | BelongsToTenant, TracksAuditColumns | ✅ |
| Mapel | `Modules/Academic/Models/Mapel.php` | BelongsToTenant, TracksAuditColumns | ✅ |
| MapelJenis | `Modules/Academic/Models/MapelJenis.php` | BelongsToTenant, TracksAuditColumns | ✅ |
| TahunAjaran | `Modules/Academic/Models/TahunAjaran.php` | BelongsToTenant, TracksAuditColumns | ✅ |
| Semester | `Modules/Academic/Models/Semester.php` | BelongsToTenant, TracksAuditColumns | ✅ |
| OrangTua | `Modules/Academic/Models/OrangTua.php` | BelongsToTenant, TracksAuditColumns | ✅ |
| SiswaOrangTua | `Modules/Academic/Models/SiswaOrangTua.php` | BelongsToTenant | ✅ |
| KelasSiswa | `Modules/Academic/Models/KelasSiswa.php` | BelongsToTenant, TracksAuditColumns | ✅ |
| Jadwal | `Modules/Academic/Models/Jadwal.php` | BelongsToTenant, TracksAuditColumns | ✅ |

### 3.2 Migrations — 11/11 ✅

| Tabel | Migration File | Keterangan |
|-------|---------------|------------|
| siswa | `0001_01_01_200013_create_siswa_table.php` | Lokal |
| guru | `0001_01_01_200013_create_guru_table.php` | Lokal |
| kelas | `0001_01_01_200013_create_kelas_table.php` | Lokal |
| semester | `2026_06_20_000102_create_semester_table.php` | Lokal |
| orang_tua | `2026_06_20_000103_create_orang_tua_table.php` | Lokal |
| siswa_orang_tua | `2026_06_20_000105_create_siswa_orang_tua_table.php` | Lokal |
| kelas_siswa | `2026_06_20_000108_create_kelas_siswa_table.php` | Lokal |
| jadwal | `2026_06_20_000110_create_jadwal_table.php` | Lokal |
| tahun_ajaran | `0001_01_01_200001_create_academic_years_table.php` | Global migration |
| mapel_jenis | `0001_01_01_200005_create_subject_types_table.php` | Global migration |
| mapel | `0001_01_01_200006_create_subjects_table.php` | Global migration |

### 3.3 Services — 2/2 ✅

| Service | File | Fungsi |
|---------|------|--------|
| JadwalConflictChecker | `Modules/Academic/Services/JadwalConflictChecker.php` | Validasi bentrok kelas & guru pada slot jadwal |
| KelasSiswaPromotionService | `Modules/Academic/Services/KelasSiswaPromotionService.php` | Promosi massal siswa antar kelas (idempotent, transactional) |

### 3.4 Policies — 4/4 ✅

| Policy | File | Permission Keys |
|--------|------|-----------------|
| SiswaPolicy | `Modules/Academic/Policies/SiswaPolicy.php` | `student.*`, `student.view` |
| GuruPolicy | `Modules/Academic/Policies/GuruPolicy.php` | `employee.*`, `employee.view` |
| KelasPolicy | `Modules/Academic/Policies/KelasPolicy.php` | `master.classroom.*`, `student.view`, `employee.view` |
| JadwalPolicy | `Modules/Academic/Policies/JadwalPolicy.php` | `academic.schedule.*`, `academic.schedule.view` |

### 3.5 Controller — 1/10 ⚠️

| Controller | File | CRUD | Gate Auth |
|------------|------|------|-----------|
| SiswaController | `Modules/Academic/Controllers/SiswaController.php` | Full (index/create/store/show/edit/update/destroy) | ✅ |

### 3.6 Views — 1 entity ⚠️

| View | File | Field ACL |
|------|------|-----------|
| siswa/index | `resources/views/academic/siswa/index.blade.php` | ✅ @field |
| siswa/create | `resources/views/academic/siswa/create.blade.php` | ✅ @field, @fieldAttr |
| siswa/show | `resources/views/academic/siswa/show.blade.php` | ✅ @field |
| siswa/edit | `resources/views/academic/siswa/edit.blade.php` | ✅ @field, @fieldAttr |

### 3.7 Requests — 2/2 (untuk Siswa saja)

| Request | File | Unique Validation |
|---------|------|-------------------|
| StoreSiswaRequest | `Modules/Academic/Requests/StoreSiswaRequest.php` | NIS & NISN unique per tenant |
| UpdateSiswaRequest | `Modules/Academic/Requests/UpdateSiswaRequest.php` | NIS & NISN unique per tenant, ignore self |

### 3.8 Factories — 5/11

| Factory | File |
|---------|------|
| SiswaFactory | `database/factories/SiswaFactory.php` |
| GuruFactory | `database/factories/GuruFactory.php` |
| KelasFactory | `database/factories/KelasFactory.php` |
| MapelFactory | `database/factories/MapelFactory.php` |
| TahunAjaranFactory | `database/factories/TahunAjaranFactory.php` |

---

## 4. Komponen yang BELUM Diimplementasi

### 4.1 Missing Controllers (9 dari 10)

| Entity | Controller | Views | Routes |
|--------|------------|-------|--------|
| Guru | ❌ | ❌ | ❌ |
| OrangTua | ❌ | ❌ | ❌ |
| TahunAjaran | ❌ | ❌ | ❌ |
| Semester | ❌ | ❌ | ❌ |
| Kelas | ❌ | ❌ | ❌ |
| KelasSiswa | ❌ | ❌ | ❌ |
| Mapel | ❌ | ❌ | ❌ |
| MapelJenis | ❌ | ❌ | ❌ |
| Jadwal | ❌ | ❌ | ❌ |

**Routes saat ini** (`Modules/Academic/routes.php`):
```php
Route::middleware(['web', 'auth'])->prefix('academic')->name('academic.')->group(function () {
    Route::resource('siswa', SiswaController::class);
    // Tidak ada route lain
});
```

### 4.2 Missing Factories (6 dari 11)

Tanpa factory: `Semester`, `OrangTua`, `SiswaOrangTua`, `KelasSiswa`, `MapelJenis`, `Jadwal`

---

## 5. BUGS Ditemukan

### 5.1 🔴 CRITICAL: Status Enum Mismatch

**File**: `database/migrations/0001_01_01_200013_create_siswa_table.php:28`
```php
$table->enum('status', ['aktif', 'lulus', 'pindah', 'keluar'])->default('aktif');
```

**File**: `Modules/Academic/Requests/StoreSiswaRequest.php:38`
```php
'status' => ['required', 'in:aktif,nonaktif,lulus,pindah,keluar']
//                                    ^^^^^^^^ TIDAK ADA DI ENUM
```

**Dampak**: Request dengan `status=nonaktif` lolos validasi tapi crash di database dengan MySQL enum error.

**Fix**: Tambahkan `nonaktif` ke enum di migration, atau hapus dari validation.

### 5.2 🔴 Guru Migration Missing softDeletes()

**Model** (`Guru.php:9`): `use SoftDeletes;`  
**Migration** (`0001_01_01_200013_create_guru_table.php`): Tidak ada `$table->softDeletes()`

```php
// Migration guru — TIDAK ADA softDeletes
$table->boolean('aktif')->default(true);
$table->timestamps();
// Missing: $table->softDeletes();
```

**Dampak**: `$guru->delete()` akan hard-delete. `$guru->trashed()` akan throw error.

**Fix**: Tambahkan `$table->softDeletes();` ke migration guru.

### 5.3 🟡 Employee Model Tanpa Tenant Isolation

**File**: `app/Models/Employee.php`
```php
class Employee extends Model
{
    use HasFactory, SoftDeletes;  // TIDAK ADA BelongsToTenant!
}
```

**Dampak**: Query Employee mengembalikan data dari SEMUA tenant — security hole.

**Fix**: Tambahkan `BelongsToTenant` trait ke Employee model.

### 5.4 🟡 Legacy Model Duplikasi

Model lama masih ada di `app/Models/`:
- `Student.php` → table `siswa` (sama dengan `Modules/Academic/Models/Siswa.php`)
- `Classroom.php` → table `kelas` (sama dengan `Modules/Academic/Models/Kelas.php`)
- `Subject.php` → table `mapel` (sama dengan `Modules/Academic/Models/Mapel.php`)
- `Employee.php` → table `employees` (berbeda dari `guru`)

Student, Classroom, Subject sekarang sudah menggunakan `BelongsToTenant` (diperbaiki setelah DEV_DOCS-050 audit), tapi duplikasi model tetap ada dan berpotensi menyebabkan kebingungan.

---

## 6. Coverage Matrix

| Category | Spec | Implemented | % |
|----------|------|-------------|---|
| Models | 11 | 11 | 100% |
| Migrations | 11 | 11 | 100% |
| Controllers | 10 | **1** | **10%** |
| Views | 10 sets | **1** set | **10%** |
| Routes | 10 | **1** | **10%** |
| Policies | 4 | 4 | 100% |
| Services | 2 | 2 | 100% |
| Tests | ~13 | 12 | 92% |
| Factories | 11 | 5 | 45% |

---

## 7. Kesimpulan

| Aspek | Penilaian |
|-------|-----------|
| Data layer (models, migrations) | ✅ Solid, tested |
| Business logic (services) | ✅ Functional, tested |
| Security (policies, tenant isolation) | ✅ Untuk model yang ada policy-nya |
| CRUD completeness | ❌ Hanya Siswa (1/10) |
| Presentation layer (views) | ❌ Hanya Siswa |
| No mockup data | ✅ Tidak ada mockup/hardcoded data |
| No hallucination | ✅ Semua kode verified ada di filesystem |

**Verdict**: Walkthrough DEV_DOCS-026 yang mengklaim "COMPLETED" hanya memverifikasi data layer. Full CRUD stack (controller + views + routes) baru terimplementasi untuk Siswa.

---

## 8. Rekomendasi Perbaikan (Priority Order)

1. **🔴 Fix enum mismatch** — Tambah `nonaktif` ke migration siswa status enum
2. **🔴 Fix guru softDeletes** — Tambah `$table->softDeletes()` ke migration guru
3. **🟡 Fix Employee tenant isolation** — Tambah `BelongsToTenant` ke Employee model
4. **🟡 Bangun 9 controller + views + routes** — Guru, Kelas, Mapel, Jadwal, TahunAjaran, Semester, OrangTua, KelasSiswa, MapelJenis
5. **🟢 Tambah 6 factory** — Untuk test coverage masa depan
6. **🟢 Bersihkan legacy model** — Consolidate Student/Classroom/Subject ke modular equivalents
