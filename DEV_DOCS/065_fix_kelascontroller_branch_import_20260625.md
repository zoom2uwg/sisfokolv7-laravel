# Fix: KelasController Branch Import Error

**Tanggal:** 2026-06-25
**Error:** Class "App\Modules\Academic\Models\Branch" not found
**URL:** http://127.0.0.1:8000/academic/kelas
**Status:** ✅ FIXED

---

## Masalah

KelasController mengimport `Branch` dari namespace yang **SALAH**:

```php
// ❌ WRONG
use App\Modules\Academic\Models\Branch;
```

Tapi Branch model sebenarnya berada di:

```php
// ✅ CORRECT
use App\Modules\Tenancy\Models\Branch;
```

---

## Solusi

### File: `app/Modules/Academic/Controllers/KelasController.php`

**Sebelum:**
```php
use App\Modules\Academic\Models\Kelas;
use App\Modules\Academic\Models\Guru;
use App\Modules\Academic\Models\Branch;  // ❌ WRONG NAMESPACE
use App\Support\Crudlfix\Crudlfix;
```

**Sesudah:**
```php
use App\Modules\Academic\Models\Kelas;
use App\Modules\Academic\Models\Guru;
use App\Modules\Tenancy\Models\Branch;  // ✅ CORRECT NAMESPACE
use App\Support\Crudlfix\Crudlfix;
```

---

## Verifikasi

✅ Syntax PHP check: No errors  
✅ File path verified: `app\Modules\Tenancy\Models\Branch.php` exists

---

## Testing

**Sebelum fix:**
```
GET /academic/kelas
500 Internal Server Error
Class "App\Modules\Academic\Models\Branch" not found
```

**Sesudah fix:**
```
GET /academic/kelas
200 OK (jika authenticated dan authorized)
```

---

## Root Cause

- Branch model adalah bagian dari **Tenancy module** (multi-tenant infrastructure)
- Kelas model menggunakan Branch sebagai relationship (cabang sekolah)
- Import path salah → Laravel tidak bisa menemukan class

---

## Referensi

| File | Keterangan |
|------|------------|
| `app/Modules/Academic/Controllers/KelasController.php` | Controller yang difix |
| `app/Modules/Academic/Models/Kelas.php` | Model yang menggunakan Branch relationship |
| `app/Modules/Tenancy/Models/Branch.php` | Model Branch (lokasi yang benar) |
| `app/Modules/Tenancy/Database/Migrations/*_create_branches_table.php` | Migration untuk branches |
