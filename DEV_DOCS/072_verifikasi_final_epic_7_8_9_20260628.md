# DEV_DOCS-072: Dev Report — Verifikasi Final EPIC 7, 8, 9 (2026-06-28)

- **Tanggal:** 2026-06-28
- **Penulis:** ZCode Agent
- **Proyek:** SISFOKOL v7 → Laravel 11 (`sisfokol-laravel/`)
- **Dasar:** Plan report `071_plan_report_eksekusi_epic_7_8_9_20260628.md` + dev report `070_dev_report_status_real_epic_7_8_9_20260628.md`
- **Status:** ✅ **EPIC 7, 8, 9 SELESAI & TERVERIFIKASI** — semua test PASS, isolasi DB terkonfirmasi aman, 2 bug real ditemukan & diperbaiki.

---

## 1. Ringkasan Eksekutif

Eksekusi plan `071` (4 fase) telah selesai. Ketiga EPIC berubah dari **"Selesai dengan catatan"** → **"Selesai & terverifikasi"**.

| EPIC | Sebelum (audit 061) | Sesudah (verifikasi 072) | Delta Test |
|:----:|:-------------------:|:------------------------:|:----------:|
| 7 — Finance | 11/14 pass (3 fail, DB config) | **14/14 PASS** (35 assertions) | +0 (fix) |
| 8 — Presence | 0 test files (audit) / 8 pass (verifikasi 070) | **11/11 PASS** (31 assertions) | **+3 test baru** |
| 9 — Kurikulum | 0/3 pass (audit) / 14 pass (verifikasi 070) | **20/20 PASS** (40 assertions) | **+6 test baru + 2 bug fix** |
| **Gabungan** | — | **45/45 PASS (106 assertions)** | **+9 test, 2 bug fix** |

**Plus:** Database produksi `sisfokol_laravel` terverifikasi **tidak tersentuh** (27 users, 0 kurikulum test data bocor) setelah full suite dijalankan.

---

## 2. Hasil Eksekusi per Fase

### Fase 0 — Environment Preparation ✅

| Task | Hasil |
|------|:-----:|
| MySQL/Laragon aktif | ✅ MySQL 5.7.44 |
| DB `sisfokol_laravel_test` tersedia | ✅ Sudah ada (0 tables → diisi RefreshDatabase) |
| Smoke test TenantContextTest | ✅ 5/5 PASS (9 assertions) |
| Snapshot test list EPIC 7/8/9 | ✅ 37 test methods tercatat |

### Fase 1 — EPIC 7 Finance ✅

| Test Class | Methods | Hasil |
|-----------|:-------:|:-----:|
| PembayaranServiceTest | 6 | ✅ 6/6 PASS |
| TabunganMutasiTest | 5 | ✅ 5/5 PASS |
| TagihanGeneratorTest | 3 | ✅ 3/3 PASS |
| **Total** | **14** | **✅ 14/14 PASS (35 assertions)** |

**Temuan:** Perbaikan `phpunit.xml` (DB test terpisah `sisfokol_laravel_test`) sudah menyelesaikan masalah 3 test fail dari audit `061`. Tidak perlu perubahan kode.

### Fase 2 — EPIC 8 Presence ✅

| Test Class | Methods | Hasil |
|-----------|:-------:|:-----:|
| IzinApprovalTest | 4 | ✅ 4/4 PASS |
| QrScanTest | 4 | ✅ 4/4 PASS |
| **AbsensiBulkStoreTest (BARU)** | **3** | **✅ 3/3 PASS (11 assertions)** |
| **Total** | **11** | **✅ 11/11 PASS (31 assertions)** |

**Test baru ditambahkan:** `tests/Feature/Presence/AbsensiBulkStoreTest.php` — mencakup 3 skenario kritis `AbsensiController::store()`:
1. Bulk store hanya buat Absence untuk status non-hadir
2. Idempotency — store ulang tanggal sama overwrite (tidak duplikasi)
3. Mapping status → type (ijin→permission, sakit→sick)

### Fase 3 — EPIC 9 Kurikulum ✅ (2 bug ditemukan & diperbaiki)

| Test Class | Methods | Hasil |
|-----------|:-------:|:-----:|
| EnsurePluginEnabledTest | 3 | ✅ 3/3 PASS |
| PluginActivationTest | 4 | ✅ 4/4 PASS |
| PluginRegistryTest | 4 | ✅ 4/4 PASS |
| KurikulumPluginTest | 3 | ✅ 3/3 PASS |
| **KurikulumCrudTest (BARU)** | **6** | **✅ 6/6 PASS (13 assertions)** |
| **Total** | **20** | **✅ 20/20 PASS (40 assertions)** |

**Test baru ditambahkan:** `tests/Feature/Plugin/KurikulumCrudTest.php` — integration test CRUD controller pasca-refactor CRUDLFIX: index, store, unique validation, update, destroy (soft delete), tenant isolation.

### Fase 4 — Full Suite ✅

| Verifikasi | Hasil |
|-----------|:-----:|
| Combined EPIC 7+8+9 | ✅ **45/45 PASS (106 assertions, 70.61s)** |
| DB produksi `sisfokol_laravel` utuh | ✅ 27 users, 0 kurikulum bocor |
| Full project test suite | ⚠️ 142 passed, 2 failed (EPIC 5, di luar scope) |

---

## 3. Bug Real yang Ditemukan & Diperbaiki (EPIC 9)

Test `KurikulumCrudTest` yang baru menemukan **2 bug** dari refactor CRUDLFIX (DEV_DOCS-064). Keduanya diperbaiki:

### Bug #1 — View Variable Mismatch (3 controller)

**Gejala:** `Undefined variable $kurikulumList` saat akses `/kurikulum` (index).
**Root cause:** View `index.blade.php` pakai `$kurikulumList`, `$strukturList`, `$komponenList` — tapi Crudlfix trait default-nya passing `$kurikulums`, `$strukturKurikulums`, `$komponenKompetensis` (dari `Str::camel(Str::plural(class_basename($model)))`).
**Fix:** Set `varName` eksplisit di 3 controller:
- `app/Plugins/Kurikulum/Controllers/KurikulumController.php` → `'varName' => 'kurikulumList'`
- `app/Plugins/Kurikulum/Controllers/StrukturKurikulumController.php` → `'varName' => 'strukturList'`
- `app/Plugins/Kurikulum/Controllers/KomponenKompetensiController.php` → `'varName' => 'komponenList'`

### Bug #2 — `{{id}}` Placeholder Tidak Di-resolve di Crudlfix

**Gejala:** Update kurikulum tidak mengubah data (`nama_kurikulum` tetap "Old Name").
**Root cause:** `Crudlfix::validateCrudlfix()` pakai `$request->validate($rules)` langsung tanpa resolve placeholder `{{id}}` di rule `unique:kurikulum,kurikulum_id,{{id}}`. Rule jadi invalid → validasi gagal → redirect back tanpa update.
**Fix:** Tambah resolve placeholder di `app/Support/Crudlfix/Crudlfix.php::validateCrudlfix()`:
```php
if ($model && $model->getKey()) {
    $id = $model->getKey();
    $rules = collect($rules)->map(function ($rule) use ($id) {
        return is_string($rule) ? str_replace('{{id}}', $id, $rule) : $rule;
    })->all();
}
```
**Dampak:** Fix ini berlaku untuk **semua controller Crudlfix** di project, bukan hanya Kurikulum — mencegah bug serupa di controller lain yang pakai pattern `unique:...,{{id}}`.

---

## 4. File yang Dibuat / Dimodifikasi

### Dibuat Baru (test files)
| File | Tests | Status |
|------|:-----:|:------:|
| `tests/Feature/Presence/AbsensiBulkStoreTest.php` | 3 | ✅ PASS |
| `tests/Feature/Plugin/KurikulumCrudTest.php` | 6 | ✅ PASS |

### Dimodifikasi (bug fix)
| File | Perubahan | Alasan |
|------|-----------|--------|
| `app/Plugins/Kurikulum/Controllers/KurikulumController.php` | +`'varName' => 'kurikulumList'` | Bug #1 |
| `app/Plugins/Kurikulum/Controllers/StrukturKurikulumController.php` | +`'varName' => 'strukturList'` | Bug #1 |
| `app/Plugins/Kurikulum/Controllers/KomponenKompetensiController.php` | +`'varName' => 'komponenList'` | Bug #1 |
| `app/Support/Crudlfix/Crudlfix.php` | Resolve `{{id}}` placeholder di `validateCrudlfix()` | Bug #2 |

---

## 5. Verifikasi Isolasi Database (Critical Safety)

| Aspek | Sebelum Test | Sesudah Full Suite | Verdict |
|-------|:------------:|:------------------:|:-------:|
| DB produksi `sisfokol_laravel.users` | 27 rows | 27 rows | ✅ Aman |
| DB produksi `sisfokol_laravel.kurikulum` | 0 rows | 0 rows | ✅ Aman |
| DB test `sisfokol_laravel_test` | 0 tables | 102 tables (RefreshDatabase) | ✅ Sesuai |

**Konfigurasi `phpunit.xml`:** `DB_DATABASE=sisfokol_laravel_test` (terpisah dari produksi). Risiko "drop tabel produksi" dari audit `061` **sudah tereliminasi**.

---

## 6. Temuan di Luar Scope (Sisa Debt untuk EPIC 5 / 12)

Full project test suite menemukan **2 test fail di EPIC 5 (Academic)** — di luar scope plan `071`:

| Test | Error | Probable Cause |
|------|-------|----------------|
| `SiswaCrudTest::tenant isolation on siswa` | expect 403, dapat 404 | Route model binding return 404 sebelum policy check (cross-tenant access) |
| `ScheduleTest::admin can create schedule` | `Call to a member function call() on null` di FormRequest:203 | Validation container tidak ter-resolve di test context |

**Rekomendasi:** Masuk ke plan report EPIC 5 fix (atau bundle ke EPIC 12 Testing). Tidak mempengaruhi status EPIC 7/8/9.

---

## 7. Update Status EPIC Lengkap (per 072)

| EPIC | Status Sebelum (069) | Status Sesudah (072) |
|:----:|:--------------------:|:--------------------:|
| 1 — Setup & Fondasi | ✅ Selesai | ✅ Selesai |
| 2 — Auth Module | ✅ Selesai | ✅ Selesai |
| 3 — RBAC Builder | ✅ Selesai | ✅ Selesai |
| 4 — Plugin Infra | ✅ Selesai | ✅ Selesai |
| 5 — Academic | ✅ Selesai | ⚠️ Selesai + 2 test fail (debt) |
| 6 — Evaluation | ✅ Selesai | ✅ Selesai |
| 7 — Finance | ⚠️ Selesai + catatan | **✅ Selesai & terverifikasi (14/14)** |
| 8 — Presence | ⚠️ Selesai + catatan | **✅ Selesai & terverifikasi (11/11)** |
| 9 — Kurikulum | ⚠️ Selesai + refactoring | **✅ Selesai & terverifikasi (20/20)** |
| 10 — 8 Plugin Scaffold | ❌ Belum dijalankan | ❌ Belum dijalankan |
| 11 — ETL Pipeline | ❌ Belum dijalankan | ❌ Belum dijalankan |
| 12 — Testing + Deployment | ❌ Belum dijalankan | ❌ Belum dijalankan |

---

## 8. Definition of Done — Terpenuhi

| DoD (dari plan 071) | Status |
|---------------------|:------:|
| MySQL aktif & DB test tersedia | ✅ |
| Seluruh test EPIC 7/8/9 PASS | ✅ 45/45 |
| Tidak ada test akses DB produksi | ✅ |
| Inconsistency lokasi model — diputuskan catat sebagai debt (risiko > value) | ✅ Didokumentasikan |
| Catat hasil verifikasi di dev report | ✅ (dokumen ini) |
| `php artisan test` jalan tanpa drop tabel produksi | ✅ |
| Coverage service kritis terverifikasi | ✅ PembayaranService, QrScannerService, AbsensiController, KurikulumController |
| Snapshot status final dicatat | ✅ (tabel §7) |

---

## 9. Rekomendasi Langkah Berikutnya

1. **Commit** perubahan Fase 3 (2 bug fix + 2 test file baru) — penting karena fix Crudlfix berlaku global.
2. **Plan report EPIC 5 fix** untuk 2 test fail `SiswaCrudTest` + `ScheduleTest` (atau bundle ke EPIC 12).
3. **Lanjut ke EPIC 10** (8 Plugin Scaffold) — sesuai urutan di `069`, EPIC 7/8/9 sudah unblock.
4. **Update `069_dev_report_status_epic_lengkap`** untuk refleksi status baru EPIC 7/8/9.

---

## 10. Evidence (Command Output Key Points)

```
# Fase 1 — EPIC 7 Finance
Tests: 14 passed (35 assertions) — Duration: 54.93s

# Fase 2 — EPIC 8 Presence (dengan test baru)
Tests: 11 passed (31 assertions) — Duration: 50.72s

# Fase 3 — EPIC 9 Kurikulum (dengan test baru + bug fix)
Tests: 20 passed (40 assertions) — Duration: 63.54s

# Fase 4 — Combined EPIC 7+8+9
Tests: 45 passed (106 assertions) — Duration: 70.61s

# DB produksi aman setelah full suite
users_count: 27 (tidak berubah)
kurikulum_count: 0 (tidak ada test data bocor)

# Full project suite (indikator EPIC 12)
Tests: 2 failed, 142 passed (339 assertions) — Duration: 160.48s
2 fail di EPIC 5 (di luar scope), bukan regression dari fix Crudlfix
```

---

*Dokumen ini menutup eksekusi plan `071`. EPIC 7, 8, 9 berstatus Selesai & Terverifikasi.*
