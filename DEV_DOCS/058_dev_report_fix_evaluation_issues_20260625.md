# Dev Report 058 — Fix 5 Issues: Evaluation Module (EPIC 6)

**Tanggal:** 2026-06-25  
**Waktu:** 15:26 – 15:35 WIB  
**Developer:** AI Assistant (Antigravity)  
**Referensi Plan:** `DEV_DOCS/057_implementation_plan_fix_evaluation_issues_20260625.md`  
**Status:** ✅ SELESAI — 5/5 Fix Diterapkan

---

## Ringkasan

| # | Fix | Severity | Status |
|---|---|---|---|
| 1 | Rapor PDF pakai `SchoolProfile` dari DB | 🔴 | ✅ DONE |
| 2 | `BatchGradeRequest::authorize()` role-based | 🔴 | ✅ DONE |
| 3 | Curriculum views → `layouts.app` | 🟡 | ✅ DONE |
| 4 | Buat `GradePolicy` & `RaporPolicy` | 🟡 | ✅ DONE |
| 5 | EvaluationFrameworkResolver — verifikasi | 🟡 | ✅ CONFIRMED OK |

---

## Fix 1 — Rapor PDF: Ganti Hardcoded Data

### File yang diubah:
- **`app/Modules/Evaluation/Services/RaporGeneratorService.php`**
  - Import `App\Models\SchoolProfile`
  - Tambah `$schoolProfile = SchoolProfile::first()` di `getReportData()`
  - Pass `schoolProfile` ke return array

- **`resources/views/evaluation/rapor/pdf.blade.php`**
  - Ganti `SMA DEMO SISFOKOL` → `{{ strtoupper($schoolProfile?->name ?? 'NAMA SEKOLAH') }}`
  - Ganti alamat hardcoded → field dari `$schoolProfile`
  - Ganti `SMA Demo Sisfokol` di info tabel → `$schoolProfile->name`
  - Ganti `Kota Demo` di TTD → `$schoolProfile->city`
  - Ganti `Dr. H. Ahmad Fauzi, M.Pd.` → `$schoolProfile->headmaster_name`
  - Ganti NIP hardcoded → `$schoolProfile->headmaster_nip`

Semua field menggunakan null-safe operator (`?->`) dengan fallback string, sehingga tidak crash jika `SchoolProfile` kosong.

---

## Fix 2 — BatchGradeRequest: Authorization

### File yang diubah:
- **`app/Modules/Evaluation/Requests/BatchGradeRequest.php`**

```php
// SEBELUM:
public function authorize(): bool { return true; }

// SESUDAH:
public function authorize(): bool
{
    $user = $this->user();
    if (!$user) return false;
    if ($user->isSuperAdmin()) return true;
    if ($user->hasRole(['admin_sekolah', 'admin'])) return true;
    if ($user->tipe === 'pegawai') return true;
    return false;
}
```

Role yang diizinkan: SuperAdmin, admin_sekolah, admin, pegawai (guru).  
Siswa, orang tua, dan tamu → 403 Forbidden.

---

## Fix 3 — Curriculum Views: Layout Konsistensi

### File yang diubah:
- **`resources/views/evaluation/curriculum/index.blade.php`**
  - `@extends('layouts.adminlte')` → `@extends('layouts.app')`
  - Update class AdminLTE: `badge-secondary` → `bg-secondary`, `btn-block` → `w-100`, `data-dismiss` → `data-bs-dismiss`, dll.

- **`resources/views/evaluation/curriculum/create.blade.php`**
  - `@extends('layouts.adminlte')` → `@extends('layouts.app')`
  - Update class: `card-primary card-outline` → `card`, `btn-default` → `btn-secondary`
  - Hapus `<section class="content">` dan `container-fluid` AdminLTE wrapper

Sekarang semua 6 view di `evaluation/` menggunakan `layouts.app` secara konsisten.

---

## Fix 4 — GradePolicy & RaporPolicy

### File baru:
- **`app/Modules/Evaluation/Policies/GradePolicy.php`**
  - `viewAny()`: admin + pegawai
  - `store()`: admin + pegawai
  - `calculate()`: admin only

- **`app/Modules/Evaluation/Policies/RaporPolicy.php`**
  - `viewAny()`: admin + wali_kelas + pegawai
  - `view()`: admin + wali_kelas + pegawai + siswa
  - `download()`: admin + wali_kelas only

### File diubah:
- **`app/Providers/AppServiceProvider.php`**
  - Import `StudentSemesterScore`, `GradePolicy`, `RaporPolicy`
  - `Gate::policy(StudentSemesterScore::class, GradePolicy::class)`
  - `Gate::define('rapor.viewAny', ...)`, `Gate::define('rapor.view', ...)`, `Gate::define('rapor.download', ...)`

---

## Fix 5 — EvaluationFrameworkResolver: Verifikasi

`EvaluationFrameworkResolver::resolve()` sudah benar:
```php
public function resolve(Mapel $mapel, ?Kelas $kelas = null): ?array
{
    $event = new EvaluationResolveFramework($mapel, $kelas);
    event($event);       // ← sudah dispatch event
    return $event->framework;
}
```

Service ini dipanggil oleh plugin test (`KurikulumPluginTest`). Tidak perlu perubahan — **status: CONFIRMED OK**.

---

## Verification

```
php83 artisan config:clear  → OK
php83 artisan route:list --path=evaluation → 10 routes, no error
PHP syntax check → Syntax OK
```

Route yang terdaftar:
| Method | Path | Controller |
|---|---|---|
| POST | evaluation/assessments/store | GradeEntryController |
| GET | evaluation/curriculum | CurriculumController@index |
| POST | evaluation/curriculum | CurriculumController@store |
| GET | evaluation/curriculum/create | CurriculumController@create |
| GET | evaluation/grade-entry | GradeEntryController@index |
| GET | evaluation/grade-entry/form | GradeEntryController@form |
| POST | evaluation/grade-entry/save | GradeEntryController@storeScores |
| GET | evaluation/rapor | RaporController@index |
| GET | evaluation/rapor/{student} | RaporController@show |
| GET | evaluation/rapor/{student}/pdf | RaporController@downloadPdf |

---

*Laporan ini dibuat oleh AI Assistant Antigravity — 2026-06-25*
