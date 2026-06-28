# DEV_DOCS-070: Dev Report — Status Real EPIC 7, 8, 9 (Verifikasi 2026-06-28)

- **Tanggal:** 2026-06-28
- **Penulis:** ZCode Agent
- **Proyek:** SISFOKOL v7 → Laravel 11 (`sisfokol-laravel/`)
- **Tujuan:** Mencatat status real (ground truth) EPIC 7 (Finance), 8 (Presence), 9 (Kurikulum) per tanggal ini, sebagai dasar plan report eksekusi penyelesaian.
- **Metode:** Pemeriksaan file fisik + konfigurasi + test files + percobaan eksekusi test suite.

---

## 1. Ringkasan Eksekutif

Sejak audit ground-truth `061` (2026-06-25), ada **perubahan positif** yang belum tercatat:

1. **`phpunit.xml` sudah diperbaiki** — test tidak lagi mengakses MySQL produksi. Sekarang pakai database test terpisah `sisfokol_laravel_test`.
2. **EPIC 8 (Presence) sudah punya test files** — audit `061` melaporkan 0 test; verifikasi hari ini menemukan **2 test files** (`IzinApprovalTest`, `QrScanTest`).
3. **EPIC 9 (Kurikulum) sudah di-refactor** ke pattern CRUDLFIX (report `064`, commit `ba3b396`) + hybrid Crudlfix + Livewire (commit `0efdbbc`).

**Namun:** Test suite **belum bisa diverifikasi PASS/FAIL** pada sesi ini karena **MySQL tidak aktif** (Laragon mati → `Connection refused`).

| EPIC | Kode Ada | Test Files | Status Test | Verdict |
|:----:|:--------:|:----------:|:-----------:|:-------:|
| 7 — Finance | ✅ 26/26 | 3 files | ⏳ Belum bisa verifikasi (MySQL mati) | Selesai + perlu verifikasi |
| 8 — Presence | ✅ 14/14 | 2 files | ⏳ Belum bisa verifikasi (MySQL mati) | Selesai + perlu verifikasi |
| 9 — Kurikulum | ✅ 28/28 | 1 file (+3 plugin infra) | ⏳ Belum bisa verifikasi (MySQL mati) | Selesai + di-refactor + perlu verifikasi |

---

## 2. Temuan Konfigurasi `phpunit.xml` (Perubahan sejak Audit 061)

### 2.1 Kondisi saat Audit 061 (2026-06-25)

```
phpunit.xml:
  <!-- <env name="DB_CONNECTION" value="sqlite"/> -->     ← DIKOMENTARI
  <!-- <env name="DB_DATABASE" value=":memory:"/> -->     ← DIKOMENTARI
```
**Akibat:** Test berjalan di MySQL produksi `sisfokol_laravel` → `RefreshDatabase` drop tabel produksi → test berikutnya gagal + data produksi hilang.

### 2.2 Kondisi Sekarang (2026-06-28)

```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="sisfokol_laravel_test"/>
```
**Perbaikan:** Test sekarang mengakses database **terpisah** `sisfokol_laravel_test`, BUKAN produksi `sisfokol_laravel`. Risiko "drop tabel produksi" sudah tereliminasi.

### 2.3 Catatan

- Koneksi masih `mysql` (bukan SQLite `:memory:`). Pemindaian migration **tidak menemukan fitur MySQL-specific** (engine/charset/collation/JSON/FULLTEXT) yang mewajibkan MySQL — sehingga opsi migrasi ke SQLite `:memory:` tetap terbuka untuk isolasi & kecepatan test.
- Database `sisfokol_laravel_test` **belum tentu sudah dibuat** di MySQL — perlu diverifikasi saat Laragon aktif.

---

## 3. Inventaris Test Files per EPIC (Verifikasi Fisik)

### 3.1 EPIC 7 — Finance (3 test files)

```
tests/Feature/Finance/
├── PembayaranServiceTest.php
├── TabunganMutasiTest.php
└── TagihanGeneratorTest.php
```

**Audit 061 melaporkan:** 11/14 pass (3 fail karena DB config). Dengan perbaikan `phpunit.xml`, ekspektasi: 14/14 pass — **perlu verifikasi ulang**.

### 3.2 EPIC 8 — Presence (2 test files) — BARU

```
tests/Feature/Presence/
├── IzinApprovalTest.php
└── QrScanTest.php
```

**Audit 061 melaporkan:** 0 test files. **Sekarang sudah ada 2** — kemungkinan ditulis ulang setelah audit. Coverage mungkin masih kurang (audit menyebut 4 controllers + 2 services + observer + event; 2 test files belum tentu mencakup semua).

### 3.3 EPIC 9 — Kurikulum (1 test file + 3 plugin infra)

```
tests/Feature/Plugin/
├── EnsurePluginEnabledTest.php
├── KurikulumPluginTest.php          ← EPIC 9
├── PluginActivationTest.php
└── PluginRegistryTest.php
```

**Audit 061 melaporkan:** 0/3 pass (`KurikulumPluginTest`). Setelah refactor CRUDLFIX (064), test mungkin perlu disesuaikan — **perlu verifikasi ulang**.

---

## 4. Percobaan Eksekusi Test (Hasil Sesi Ini)

**Command dijalankan:**
```bash
php artisan test tests/Feature/Finance tests/Feature/Presence tests/Feature/Plugin/KurikulumPluginTest.php
```

**Hasil:** ❌ **GAGAL — bukan karena kode, tapi environment:**
```
SQLSTATE[HY000] [2002] No connection could be made because the target
machine actively refused it (Connection: mysql, ...)
```

**Root cause:** MySQL service (Laragon) **tidak berjalan**. Tidak ada koneksi ke DB test maupun produksi.

**Implikasi:** Tidak ada satu pun test yang bisa diverifikasi PASS/FAIL pada sesi ini. Verifikasi tertunda sampai MySQL aktif.

---

## 5. Refactoring & Perubahan Pasca-Audit (EPIC 9)

### 5.1 CRUDLFIX Refactoring (DEV_DOCS-064, commit `ba3b396`)

| Controller | Before | After | Reduction |
|------------|-------:|------:|:---------:|
| KurikulumController | 86 lines | 36 lines | -50 lines (58%) |
| StrukturKurikulumController | 96 lines | 50 lines | -46 lines (48%) |
| KomponenKompetensiController | 97 lines | 45 lines | -52 lines (54%) |
| **Total** | **279** | **131** | **-148 (53%)** |

**Fitur baru via CRUDLFIX:** search, filter, sort, export CSV, API endpoints, N+1 prevention.

### 5.2 Hybrid Crudlfix + Livewire (commit `0efdbbc`)

Commit recent menunjukkan integrasi Livewire v4 dengan pattern Crudlfix:
- `CrudlfixTable`, `CrudlfixForm`, `CrudlfixPage` components
- `HasCrudlfixTable`, `HasCrudlfixForm`, `HasCrudlfixActions` traits
- Pilot route untuk `KelasController` testing

**Catatan:** Refactoring ini mungkin mempengaruhi test EPIC 9 — perlu pastikan `KurikulumPluginTest` masih relevan dengan struktur controller baru.

---

## 6. Gap yang Masih Ada (Perlu Tindak Lanjut)

### 6.1 Gap Lintas-EPIC (Blocking)

| # | Gap | Dampak | Prioritas |
|---|-----|--------|:---------:|
| G1 | MySQL/Laragon tidak aktif saat sesi ini | Tidak bisa verifikasi test PASS/FAIL | **HIGH** |
| G2 | Database `sisfokol_laravel_test` belum tentu ada | Test akan gagal saat MySQL aktif | **HIGH** |
| G3 | Test suite belum diverifikasi end-to-end pasca-perbaikan `phpunit.xml` | Status real PASS/FAIL unknown | **HIGH** |

### 6.2 Gap EPIC 7 (Finance)

| # | Gap | Dampak | Prioritas |
|---|-----|--------|:---------:|
| F1 | 3 test fail (lagi-lagi) — perlu konfirmasi apakah fix `phpunit.xml` sudah cukup | Coverage tidak terverifikasi | Medium |
| F2 | Duplikasi model: `PaymentItem` (app/Models/) vs `ItemPembayaran` (app/Modules/Finance/Models/) | Inconsistency | Low |

### 6.3 Gap EPIC 8 (Presence)

| # | Gap | Dampak | Prioritas |
|---|-----|--------|:---------:|
| P1 | Coverage test mungkin kurang (2 files vs 4 controllers + 2 services) | Belum tentu mencakup semua alur | Medium |
| P2 | `SubjectAttendance` model di `app/Models/` vs `app/Modules/Presence/` | Inconsistent location | Low |
| P3 | `AttendanceService` di `app/Services/` vs `app/Modules/Presence/Services/` | Inconsistent location | Low |

### 6.4 Gap EPIC 9 (Kurikulum)

| # | Gap | Dampak | Prioritas |
|---|-----|--------|:---------:|
| K1 | `KurikulumPluginTest` 0/3 pass — perlu konfirmasi pasca-refactor CRUDLFIX | Test mungkin outdated | Medium |
| K2 | Tidak ada `Services/` directory (logic di Controllers) | Arsitektur tidak konsisten | Low |
| K3 | Test subscriber hanya cek resolve, tidak cek rapor | Coverage kurang | Low |

---

## 7. Referensi Dokumen Pendukung

| Dokumen | Isi |
|---------|-----|
| `061_audit_epic_7_8_9_ground_truth_20260625.md` | Audit ground-truth gabungan (titik awal) |
| `062_verifikasi_mendalam_epic9_kurikulum_20260625.md` | Verifikasi mendalam EPIC 9 |
| `063_audit_crudlfix_epic9_kurikulum_20260625.md` | Audit CRUDLFIX EPIC 9 |
| `064_epic9_crudlfix_refactoring_report_20260625.md` | Report refactoring EPIC 9 |
| `057_implementation_plan_fix_evaluation_issues_20260625.md` | Plan fix EPIC 6 (referensi pola) |
| `069_dev_report_status_epic_lengkap_20260628.md` | Status lengkap semua EPIC |

---

## 8. Langkah Berikutnya

Lihat **`071_plan_report_eksekusi_epic_7_8_9_20260628.md`** untuk rencana eksekusi detail penyelesaian gap EPIC 7, 8, 9.

---

*Dokumen ini adalah snapshot status real per 2026-06-28. Update setelah verifikasi test dijalankan dengan MySQL aktif.*
