# Implementation Plan 057 — Fix 5 Issues: Evaluation Module (EPIC 6)

**Tanggal:** 2026-06-25  
**Developer:** AI Assistant (Antigravity)  
**Status:** PLANNED — Menunggu Approval  
**Priority:** 🔴 Critical (Fix 1, 2) | 🟡 Medium (Fix 3, 4, 5)

---

## Latar Belakang

Audit modul Evaluation (EPIC 6) menemukan 5 isu yang perlu diperbaiki sebelum modul ini bisa dinyatakan production-ready:

| # | Isu | Severity |
|---|---|---|
| 1 | Rapor PDF hardcoded "SMA DEMO SISFOKOL", "Dr. H. Ahmad Fauzi, M.Pd." — bukan dari data tenant | 🔴 |
| 2 | `BatchGradeRequest::authorize()` returns `true` — siapapun bisa input nilai | 🔴 |
| 3 | Curriculum views pakai `layouts.adminlte`, view lain pakai `layouts.app` — inkonsistensi UI | 🟡 |
| 4 | `GradePolicy` & `RaporPolicy` tidak ada — tidak ada gate untuk aksi pada model | 🟡 |
| 5 | `EvaluationFrameworkResolver` & `RaportRenderSection` tidak pernah di-dispatch di controller | 🟡 |

---

## Fix 1 — 🔴 Rapor PDF: Ganti Hardcoded Data dengan `SchoolProfile`

### Masalah
`resources/views/evaluation/rapor/pdf.blade.php` memiliki data hardcoded:
- Baris 111: `<div class="school-name">SMA DEMO SISFOKOL</div>`
- Baris 112: `Jl. Pendidikan No. 1, Kota Demo • Telp: 021-1234567 • Email: info@smademo.sch.id`
- Baris 140: `<td>SMA Demo Sisfokol</td>`
- Baris 222: `Kota Demo, {{ now()->format('d F Y') }}`
- Baris 233: `<strong>Dr. H. Ahmad Fauzi, M.Pd.</strong>`
- Baris 234: `NIP. 197508122000031002`

### Solusi
**File: `app/Modules/Evaluation/Controllers/RaporController.php`**
- Load `SchoolProfile::first()` di method `pdf()`
- Pass `$schoolProfile` ke view

**File: `resources/views/evaluation/rapor/pdf.blade.php`**
```blade
{{-- SEBELUM (hardcoded) --}}
<div class="school-name">SMA DEMO SISFOKOL</div>
<div class="school-address">Jl. Pendidikan No. 1, Kota Demo • Telp: 021-1234567 ...</div>

{{-- SESUDAH (dari DB) --}}
<div class="school-name">{{ $schoolProfile->name }}</div>
<div class="school-address">
    {{ $schoolProfile->address }}, {{ $schoolProfile->city }} •
    Telp: {{ $schoolProfile->phone }} •
    Email: {{ $schoolProfile->email }}
</div>
```

Kolom yang dipakai dari tabel `school_profiles`:
- `name` — nama sekolah
- `address` — alamat
- `city` — kota
- `phone` — telepon
- `email` — email
- `headmaster_name` — nama kepala sekolah
- `headmaster_nip` — NIP kepala sekolah

---

## Fix 2 — 🔴 BatchGradeRequest: Tambah Authorization

### Masalah
```php
// app/Modules/Evaluation/Requests/BatchGradeRequest.php
public function authorize(): bool
{
    return true; // ← siapapun bisa input nilai!
}
```

### Solusi
```php
public function authorize(): bool
{
    $user = $this->user();
    if (!$user) return false;

    // SuperAdmin: akses penuh
    if ($user->isSuperAdmin()) return true;

    // Admin sekolah: akses penuh
    if ($user->hasRole(['admin_sekolah', 'admin'])) return true;

    // Guru (tipe pegawai): hanya boleh input nilai
    return $user->tipe === 'pegawai';
}
```

> [!IMPORTANT]
> **Open Question:** Apakah guru hanya boleh input nilai untuk kelas yang **dia ajar** (cek jadwal) saja? Atau cukup `tipe === 'pegawai'` sudah cukup?
> Saat ini `GradeEntryController::index()` sudah filter per jadwal, tapi `BatchGradeRequest` tidak cross-check ini.
> Jika strict: perlu tambah check `Schedule::where('employee_id', $user->userable_id)->where('classroom_id', $request->classroom_id)->exists()`

---

## Fix 3 — 🟡 Curriculum Views: Ganti AdminLTE → layouts.app

### Masalah
```
resources/views/evaluation/
├── rapor/show.blade.php      → @extends('layouts.app')   ✅
├── rapor/index.blade.php     → @extends('layouts.app')   ✅
├── grade-entry/index.blade.php → @extends('layouts.app') ✅
├── grade-entry/form.blade.php  → @extends('layouts.app') ✅
├── curriculum/index.blade.php  → @extends('layouts.adminlte')  ❌
└── curriculum/create.blade.php → @extends('layouts.adminlte')  ❌
```

### Solusi
**File: `views/evaluation/curriculum/index.blade.php`**
- Baris 1: ganti `@extends('layouts.adminlte')` → `@extends('layouts.app')`
- Update class-class AdminLTE ke Bootstrap 5:
  - `card-dark` → `card`
  - `badge-secondary` → `badge bg-secondary`
  - `btn-block` → `w-100`
  - `mr-2` → `me-2`
  - `data-dismiss="alert"` → `data-bs-dismiss="alert"`

**File: `views/evaluation/curriculum/create.blade.php`**
- Sama: ganti layout dan update class CSS

> [!NOTE]
> Perlu cek dahulu apakah `layouts.adminlte` masih exist atau sudah dihapus dari project.

---

## Fix 4 — 🟡 Buat GradePolicy & RaporPolicy

### Masalah
`GradePolicy` dan `RaporPolicy` tidak ada di filesystem, tapi referenced di `AppServiceProvider` (atau seharusnya ada).

### Solusi: Buat 2 File Baru

**[NEW] `app/Modules/Evaluation/Policies/GradePolicy.php`**
```php
class GradePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->hasRole(['admin_sekolah', 'admin'])
            || $user->tipe === 'pegawai';
    }

    public function store(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->hasRole(['admin_sekolah', 'admin'])
            || $user->tipe === 'pegawai';
    }

    public function calculate(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->hasRole(['admin_sekolah', 'admin']);
    }
}
```

**[NEW] `app/Modules/Evaluation/Policies/RaporPolicy.php`**
```php
class RaporPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->hasRole(['admin_sekolah', 'admin', 'wali_kelas'])
            || $user->tipe === 'pegawai';
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user)
            || $user->tipe === 'siswa'; // siswa bisa lihat rapornya sendiri
    }

    public function download(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->hasRole(['admin_sekolah', 'admin', 'wali_kelas']);
    }
}
```

**[MODIFY] `app/Providers/AppServiceProvider.php`**
```php
use App\Modules\Evaluation\Policies\GradePolicy;
use App\Modules\Evaluation\Policies\RaporPolicy;

// Di method boot():
Gate::policy(StudentSemesterScore::class, GradePolicy::class);
Gate::policy(Rapor::class, RaporPolicy::class); // atau model yang relevan
```

---

## Fix 5 — 🟡 EvaluationFrameworkResolver: Pastikan Dipanggil

### Analisis
`EvaluationFrameworkResolver::resolve()` sudah benar — ia memanggil `event($event)`. Yang perlu dicek adalah apakah method ini **dipanggil** dari `RaporController`.

### Solusi
Verifikasi `RaporController::pdf()` memanggil:
```php
$framework = app(EvaluationFrameworkResolver::class)->resolve($mapel, $kelas);
// Pass $framework ke view (untuk plugin Kurikulum jika aktif)
```

`RapartRenderSection` perlu di-dispatch di saat render view rapor agar plugin bisa inject section tambahan.

---

## Files yang Akan Diubah

| Status | File | Perubahan |
|---|---|---|
| MODIFY | `resources/views/evaluation/rapor/pdf.blade.php` | Ganti hardcoded → `$schoolProfile->*` |
| MODIFY | `app/Modules/Evaluation/Controllers/RaporController.php` | Load SchoolProfile, pass ke view |
| MODIFY | `app/Modules/Evaluation/Requests/BatchGradeRequest.php` | authorize() dengan role check |
| MODIFY | `resources/views/evaluation/curriculum/index.blade.php` | Ganti layout + class CSS |
| MODIFY | `resources/views/evaluation/curriculum/create.blade.php` | Ganti layout + class CSS |
| **NEW** | `app/Modules/Evaluation/Policies/GradePolicy.php` | Buat baru |
| **NEW** | `app/Modules/Evaluation/Policies/RaporPolicy.php` | Buat baru |
| MODIFY | `app/Providers/AppServiceProvider.php` | Daftarkan 2 policy baru |
| VERIFY | `app/Modules/Evaluation/Controllers/RaporController.php` | Pastikan EvaluationFrameworkResolver dipanggil |

---

## Verification Plan

### Automated Tests
```bash
php83 artisan test tests/Feature/Evaluation/
```

### Manual Tests
1. **Fix 1:** Download rapor PDF → nama sekolah harus dari DB `school_profiles`
2. **Fix 2:** Login sebagai Guru Piket → coba POST ke `/evaluation/grades/store` → harus 403
3. **Fix 3:** Buka `/evaluation/curriculum` sebagai guru → layout harus sama dengan halaman lain
4. **Fix 4:** Jalankan `php83 artisan route:list` → tidak ada policy error

---

*Dibuat oleh AI Assistant Antigravity — 2026-06-25*
