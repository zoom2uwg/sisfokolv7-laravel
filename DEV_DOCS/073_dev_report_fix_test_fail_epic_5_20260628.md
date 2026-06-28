# DEV_DOCS-073: Dev Report — Fix 2 Test Fail EPIC 5 (Academic) 2026-06-28

- **Tanggal:** 2026-06-28
- **Penulis:** ZCode Agent
- **Proyek:** SISFOKOL v7 → Laravel 11 (`sisfokol-laravel/`)
- **Trigger:** Full project suite pasca-fix EPIC 7/8/9 (report `072`) menemukan 2 test fail di EPIC 5.
- **Status:** ✅ **SELESAI & TERVERIFIKASI** — full project suite 144/144 PASS.

---

## 1. Ringkasan Eksekutif

2 test fail di EPIC 5 (Academic) yang muncul di full suite `072` telah **diperbaiki dan diverifikasi**. Keduanya root cause-nya terkait **Crudlfix trait** — sekeluarga dengan bug `{{id}}` placeholder yang sudah difix di `072`. Hasilnya: **full project test suite naik dari 142 → 144 PASS, 0 fail.**

| Test | Sebelum | Sesudah | Root Cause |
|------|:-------:|:-------:|------------|
| `SiswaCrudTest::tenant_isolation_on_siswa` | ❌ expect 403, dapat 404 | ✅ PASS | Test expectation salah — 404 adalah by-design |
| `ScheduleTest::admin_can_create_schedule` | ❌ `call() on null` di FormRequest:203 | ✅ PASS | Bug Crudlfix: FormRequest tidak di-resolve via container |

**Keputusan:** Fix sekarang (bukan bundle ke EPIC 12) karena:
1. Bug Crudlfix FormRequest **sekeluarga** dengan bug `{{id}}` yang sudah difix — konsisten untuk fix bersama.
2. Fix trivial (test expectation + 1 method Crudlfix).
3. Full suite 144/144 = baseline bersih untuk EPIC 10/11/12.

---

## 2. Investigasi & Root Cause

### 2.1 SiswaCrudTest::tenant_isolation_on_siswa

**Error:** `Failed asserting that 404 is identical to 403`

**Investigasi:** Baca `Crudlfix::resolveModel()` (line 56-83):
```php
if ($usesTenantTrait) {
    $model = $cfg->model::find($id);
    if (!$model) {
        // Model not found OR belongs to different tenant
        // Return 404 to avoid data leakage (don't reveal existence)
        abort(404, 'Data tidak ditemukan.');
    }
    return $model;
}
```

**Verdict:** `resolveModel()` **sengaja** `abort(404)` untuk cross-tenant access — ada komentar eksplisit "avoid data leakage, don't reveal existence". Ini **behavior by-design** yang benar secara security (404 vs 403 mencegah information disclosure). **Test expectation yang salah** — harus 404, bukan 403.

### 2.2 ScheduleTest::admin_can_create_schedule

**Error:** `Call to a member function call() on null in FormRequest.php:203`

**Investigasi:** Baca `Crudlfix::validateCrudlfix()` sebelum fix:
```php
if ($cfg->requestClass) {
    $formRequest = new $cfg->requestClass();   // ← instantiate dengan new
    return $formRequest->validateResolved($request);  // ← container null
}
```

**Root cause:** FormRequest di-instantiate dengan `new` (bukan via container) → `$this->container` null → saat `validateResolved()` panggil `$this->getValidatorInstance()` → akses `$this->container->call(...)` → null pointer.

**Verdict:** Bug Crudlfix sekeluarga dengan bug `{{id}}`. `ScheduleController` adalah satu-satunya controller yang pakai `requestClass` (FormRequest) — inilah sebabnya hanya ScheduleTest yang terkena.

---

## 3. Fix yang Diterapkan

### Fix #1 — SiswaCrudTest expectation (trivial)

**File:** `tests/Feature/Academic/SiswaCrudTest.php`
**Perubahan:** Update 3 assertion `assertStatus(403)` → `assertStatus(404)` + tambah komentar menjelaskan by-design behavior.

```php
// Note: Crudlfix::resolveModel() intentionally aborts 404 (not 403) for
// cross-tenant access to avoid revealing that the record exists.
app(TenantContext::class)->clear();
$response = $this->actingAs($this->admin1)->get("/academic/siswa/{$siswa2->id}");
$response->assertStatus(404);
```

### Fix #2 — Crudlfix FormRequest container (bug real)

**File:** `app/Support/Crudlfix/Crudlfix.php` (method `validateCrudlfix`)
**Perubahan:** Resolve FormRequest via container + wire container/redirector + merge request data + set route resolver + return `validated()`.

```php
if ($cfg->requestClass) {
    $formRequest = app($cfg->requestClass);
    $formRequest->setContainer(app())->setRedirector(app('redirect'));
    $formRequest->merge($request->input());
    $formRequest->setJson($request->json());
    $formRequest->setRouteResolver(fn () => $request->route());
    $formRequest->validateResolved();
    return $formRequest->validated();
}
```

**Catatan teknis:** `validateResolved()` return `void` (bukan array) — fix sebelumnya return `validateResolved()` langsung yang juga salah. Return yang benar adalah `$formRequest->validated()` setelah validate. Route resolver diperlukan karena `StoreScheduleRequest::rules()` pakai `$this->route('schedule')?->id` untuk unique rule.

**Dampak global:** Fix ini berlaku untuk **semua controller Crudlfix** yang pakai `requestClass` — saat ini hanya `ScheduleController`, tapi mencegah bug serupa saat EPIC 10/extension lain pakai FormRequest + Crudlfix.

---

## 4. Verifikasi Hasil

### 4.1 Test yang Diperbaiki (isolasi)

```
SiswaCrudTest: 6 passed (termasuk tenant_isolation) — Duration: ~50s
ScheduleTest: 1 passed (3 assertions) — Duration: 50.34s
```

### 4.2 Full Project Suite (end-to-end)

```
Tests: 144 passed (343 assertions) — Duration: 166.29s
```

**Sebelum (report 072):** 142 passed, 2 failed
**Sesudah (report 073):** 144 passed, 0 failed

### 4.3 No Regression Check

Full suite 144/144 = **tidak ada regression** dari fix Crudlfix. Semua test yang sebelumnya pass tetap pass, termasuk:
- EPIC 7 Finance: 14/14 ✅
- EPIC 8 Presence: 11/11 ✅
- EPIC 9 Kurikulum + Plugin: 20/20 ✅
- EPIC 5 Academic: 7/7 ✅ (termasuk 2 yang baru difix)

---

## 5. Daftar Bug Crudlfix yang Sudah Diperbaiki (Konsolidasi)

Sekarang ada **3 bug Crudlfix** total yang diperbaiki dalam sesi ini — semua sekeluarga (method `validateCrudlfix` & `resolveModel`):

| # | Bug | File | Fix di Report | Dampak |
|---|-----|------|:-------------:|--------|
| 1 | View variable mismatch (`$kurikulumList` vs default) | 3 controller Kurikulum | 072 | Index view error untuk Kurikulum/Struktur/Komponen |
| 2 | `{{id}}` placeholder tidak di-resolve di rule `unique` | `Crudlfix.php` | 072 | Update record gagal diamar (validation error) |
| 3 | FormRequest tidak di-resolve via container | `Crudlfix.php` | 073 (ini) | Controller pakai `requestClass` crash saat store/update |

**Rekomendasi untuk EPIC 12:** Tambah test Crudlfix trait sendiri (unit test) yang mencakup 3 skenario ini, agar regression terdeteksi otomatis di masa depan.

---

## 6. Update Status EPIC

| EPIC | Status Sebelum (072) | Status Sesudah (073) |
|:----:|:--------------------:|:--------------------:|
| 5 — Academic | ⚠️ Selesai + 2 test fail (debt) | **✅ Selesai & terverifikasi (7/7)** |
| 7 — Finance | ✅ Selesai & terverifikasi | ✅ Selesai & terverifikasi |
| 8 — Presence | ✅ Selesai & terverifikasi | ✅ Selesai & terverifikasi |
| 9 — Kurikulum | ✅ Selesai & terverifikasi | ✅ Selesai & terverifikasi |

**Baseline project sekarang:** 144/144 PASS. EPIC 1-9 semua ✅ Selesai & terverifikasi.

---

## 7. File yang Dimodifikasi

| File | Perubahan | Alasan |
|------|-----------|--------|
| `app/Support/Crudlfix/Crudlfix.php` | Resolve FormRequest via container + return `validated()` | Fix #2 (bug real, global) |
| `tests/Feature/Academic/SiswaCrudTest.php` | 3x `assertStatus(403)` → `assertStatus(404)` + komentar | Fix #1 (test expectation) |

---

## 8. Evidence (Command Output)

```
# Test yang diperbaiki dalam isolasi
SiswaCrudTest: 6 passed
ScheduleTest: 1 passed (3 assertions) — Duration: 50.34s

# Full project suite (konfirmasi no regression)
Tests: 144 passed (343 assertions) — Duration: 166.29s
```

---

## 9. Langkah Berikutnya

1. **Commit** semua perubahan (Crudlfix 3 bug fix + 2 test baru EPIC 8/9 + 2 test fix EPIC 5 + DEV_DOCS 069-073).
2. **Lanjut EPIC 10** (8 Plugin Scaffold) — baseline 144/144 bersih, siap eksekusi.
3. **EPIC 12 (Testing):** Tambah unit test Crudlfix trait untuk 3 skenario bug yang sudah difix — mencegah regression.

---

*Dokumen ini menutup fix EPIC 5. Project baseline: 144/144 PASS. EPIC 1-9 semua Selesai & Terverifikasi.*
