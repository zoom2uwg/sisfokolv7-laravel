# DEV_DOCS-071: Plan Report — Eksekusi Penyelesaian EPIC 7, 8, 9

- **Tanggal:** 2026-06-28
- **Penulis:** ZCode Agent
- **Proyek:** SISFOKOL v7 → Laravel 11 (`sisfokol-laravel/`)
- **Tujuan:** Rencana eksekusi terstruktur untuk menutup gap EPIC 7 (Finance), 8 (Presence), 9 (Kurikulum) agar status berubah dari "Selesai dengan catatan" → "Selesai & terverifikasi".
- **Dasar:** Dev report `070_dev_report_status_real_epic_7_8_9_20260628.md` + audit `061` + refactor report `064`.

---

## 1. Goal & Definition of Done

**Goal:** Ketiga EPIC (7, 8, 9) lulus verifikasi test suite end-to-end dengan status **100% PASS** di environment test terisolasi, tanpa mengganggu database produksi.

**Definition of Done (DoD) per EPIC:**
- [ ] MySQL/Laragon aktif & database test `sisfokol_laravel_test` tersedia
- [ ] Seluruh test files untuk EPIC tersebut PASS (0 fail)
- [ ] Tidak ada test yang mengakses database produksi `sisfokol_laravel`
- [ ] Inconsistency lokasi model/service (jika diputuskan) sudah di-reconcile
- [ ] Catat hasil verifikasi di dev report update

**DoD keseluruhan (lintas EPIC):**
- [ ] `php artisan test --suite=Feature` jalan full tanpa drop tabel produksi
- [ ] Coverage test untuk service kritis terverifikasi (PembayaranService, QrScannerService, KurikulumPlugin)
- [ ] Snapshot status final dicatat di DEV_DOCS

---

## 2. Tabel Eksekusi (Fase)

| Fase | Nama | EPIC | Prasyarat | Estimasi |
|:----:|------|:----:|-----------|:--------:|
| 0 | Environment Prep | 7,8,9 | — | 15 menit |
| 1 | Verifikasi & Stabilisasi Test EPIC 7 | 7 | Fase 0 | 30–60 menit |
| 2 | Verifikasi & Tambah Coverage EPIC 8 | 8 | Fase 0 | 45–90 menit |
| 3 | Verifikasi & Sesuaikan Test EPIC 9 | 9 | Fase 0 | 30–60 menit |
| 4 | Full Suite Run & Final Report | 7,8,9 | Fase 1–3 | 20 menit |

**Total estimasi:** 2.5–4 jam (tergantung jumlah test fail yang ditemukan).

---

## 3. Fase 0 — Environment Preparation

**Goal:** Pastikan MySQL aktif & database test tersedia sebelum test apapun dijalankan.

### Task 0.1 — Aktifkan MySQL/Laragon
- [ ] Start Laragon (atau MySQL service via `net start MySQL` / services.msc)
- [ ] Verifikasi: `mysql -u root -e "SELECT 1"` → success

### Task 0.2 — Buat database test jika belum ada
- [ ] Cek: `mysql -u root -e "SHOW DATABASES LIKE 'sisfokol_laravel_test'"`
- [ ] Jika belum ada:
  ```sql
  CREATE DATABASE sisfokol_laravel_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  ```
- [ ] Verifikasi koneksi dari Laravel:
  ```bash
  cd sisfokol-laravel
  DB_DATABASE=sisfokol_laravel_test php artisan db:show
  ```

### Task 0.3 — Smoke test koneksi
- [ ] Jalankan 1 test kecil untuk pastikan DB test terisolasi:
  ```bash
  php artisan test tests/Unit/Support/TenantContextTest.php
  ```
- [ ] Expected: PASS (TenantContextTest tidak butuh DB, validasi bootstrap test)

### Task 0.4 — Snapshot status awal
- [ ] Catat output `php artisan test --list` (daftar test) ke dev report

---

## 4. Fase 1 — EPIC 7 (Finance) Verifikasi & Stabilisasi

**Goal:** 14/14 test Finance PASS.

### Task 1.1 — Jalankan test Finance dalam isolasi
- [ ] Command:
  ```bash
  php artisan test tests/Feature/Finance
  ```
- [ ] Catat hasil: berapa pass / fail, error message

### Task 1.2 — Analisis root cause setiap fail
- [ ] Jika fail karena DB: periksa `RefreshDatabase` + koneksi `sisfokol_laravel_test`
- [ ] Jika fail karena logic: baca service terkait (`PembayaranService`, `TabunganMutasiService`, `TagihanGeneratorService`)
- [ ] Jika fail karena data seed: pastikan seeder di-setup test (`$this->seed(...)`)

### Task 1.3 — Perbaikan iteratif
- [ ] Fix satu per satu, jalankan ulang per test class:
  ```bash
  php artisan test tests/Feature/Finance/PembayaranServiceTest.php
  php artisan test tests/Feature/Finance/TabunganMutasiTest.php
  php artisan test tests/Feature/Finance/TagihanGeneratorTest.php
  ```
- [ ] Target: masing-masing 100% pass

### Task 1.4 — Pertimbangan opsional: SQLite `:memory:`
- [ ] Evaluasi: apakah lebih aman pakai SQLite untuk test? (scan migration tidak temukan MySQL-specific)
- [ ] Jika ya: uncomment di `phpunit.xml`:
  ```xml
  <env name="DB_CONNECTION" value="sqlite"/>
  <env name="DB_DATABASE" value=":memory:"/>
  ```
- [ ] Jalankan ulang — pastikan tidak ada migration yang break di SQLite
- [ ] **Keputusan:** pakai SQLite `:memory:` ATAU tetap MySQL test DB — catat alasan

### Task 1.5 — Reconcile duplikasi model (opsional, Low priority)
- [ ] Audit `061` menemukan `PaymentItem` (app/Models/) vs `ItemPembayaran` (app/Modules/Finance/Models/)
- [ ] Putuskan: hapus salah satu, atau dokumentasikan kenapa ada dua
- [ ] Update referensi di controllers/services jika dihapus

### Task 1.6 — Catat hasil Fase 1
- [ ] Update `070` (atau buat `072`) dengan: jumlah pass/fail final, fix yang dilakukan

---

## 5. Fase 2 — EPIC 8 (Presence) Verifikasi & Tambah Coverage

**Goal:** Test Presence PASS + coverage memadai untuk service kritis.

### Task 2.1 — Jalankan test Presence dalam isolasi
- [ ] Command:
  ```bash
  php artisan test tests/Feature/Presence
  ```
- [ ] Catat hasil: `IzinApprovalTest`, `QrScanTest` — pass/fail

### Task 2.2 — Analisis gap coverage
- [ ] Bandingkan test files vs komponen yang ada:
  - Controllers: `PresensiController`, `IzinController`, `AbsensiController`, `LaporanPresensiController`
  - Services: `AttendanceService`, `PresensiRuleEngine`, `QrScannerService`, `IzinApprovalService`
  - Observer: `AttendanceObserver`
  - Event: `PresenceRecorded`
- [ ] Identifikasi service kritis tanpa test → daftar untuk ditambah

### Task 2.3 — Tambah test untuk service kritis (jika gap)
- [ ] Prioritas: `QrScannerService` (scan QR → validasi siswa → anti-duplikasi) jika belum tercakup `QrScanTest`
- [ ] Prioritas: `PresensiRuleEngine` (status present/late/early) — buat `tests/Feature/Presence/PresensiRuleEngineTest.php`
- [ ] Pattern: ikuti gaya test EPIC 5/7 yang sudah ada (RefreshDatabase, actingAs, seed roles)
- [ ] Target: minimal test service kritis + 1 controller integration test

### Task 2.4 — Reconcile lokasi model/service (opsional, Low priority)
- [ ] Audit `061`: `SubjectAttendance` di `app/Models/`, `AttendanceService` di `app/Services/`
- [ ] Pertimbangkan pindah ke `app/Modules/Presence/Models/` & `app/Modules/Presence/Services/`
- [ ] **Hati-hati:** update semua `use` statements + service provider binding
- [ ] Jika risiko terlalu besar untuk value-nya → dokumentasikan sebagai debt, skip

### Task 2.5 — Catat hasil Fase 2
- [ ] Update dev report: jumlah test sebelum/sesudah, coverage delta

---

## 6. Fase 3 — EPIC 9 (Kurikulum) Verifikasi & Sesuaikan Test

**Goal:** `KurikulumPluginTest` PASS pasca-refactor CRUDLFIX + Livewire.

### Task 3.1 — Jalankan test Kurikulum dalam isolasi
- [ ] Command:
  ```bash
  php artisan test tests/Feature/Plugin/KurikulumPluginTest.php
  ```
- [ ] Catat hasil: 3 test methods — pass/fail

### Task 3.2 — Verifikasi relevansi test pasca-refactor
- [ ] Refactor `064` mengubah controller dari 279 → 131 lines (CRUDLFIX pattern)
- [ ] Baca `KurikulumPluginTest.php` — apakah assert masih cocok dengan controller baru?
- [ ] Baca commit `0efdbbc` (hybrid Livewire) — apakah ada perubahan route/behavior yang break test?

### Task 3.3 — Perbaikan/sesuaikan test
- [ ] Jika test assert pada method/property yang sudah berubah → update assert
- [ ] Jika test pakai route yang berubah (API endpoint baru) → update route di test
- [ ] Pastikan test tetap menguji esensi: plugin activation, event subscriber, tenant isolation
- [ ] Target: 3/3 pass

### Task 3.4 — Verifikasi subscriber coverage (opsional, Low priority)
- [ ] Audit `061`: `EvaluationFrameworkSubscriber` test hanya cek resolve, tidak cek rapor
- [ ] Tambah assert untuk `RaporSectionSubscriber` jika time permits
- [ ] Buat `tests/Feature/Plugin/KurikulumSubscriberTest.php` jika diperlukan

### Task 3.5 — Catat hasil Fase 3
- [ ] Update dev report: status final test, perubahan yang dilakukan

---

## 7. Fase 4 — Full Suite Run & Final Report

**Goal:** Konfirmasi end-to-end semua EPIC 7, 8, 9 pass bersamaan.

### Task 4.1 — Full Feature suite
- [ ] Command:
  ```bash
  php artisan test tests/Feature/Finance tests/Feature/Presence tests/Feature/Plugin
  ```
- [ ] Catat: total tests, pass, fail, duration

### Task 4.2 — Verifikasi database produksi tidak tersentuh
- [ ] Sebelum & sesudah test, cek:
  ```bash
  mysql -u root -e "SELECT COUNT(*) FROM sisfokol_laravel.users"
  ```
- [ ] Expected: count tidak berubah (test hanya menulis ke `sisfokol_laravel_test`)

### Task 4.3 — Full project test suite (opsional, indikator global)
- [ ] Command:
  ```bash
  php artisan test
  ```
- [ ] Catat total pass/fail lintas semua EPIC — ini juga sinyal kesiapan EPIC 12

### Task 4.4 — Final dev report
- [ ] Buat `072_verifikasi_final_epic_7_8_9_<tanggal>.md`:
  - Tabel status final per EPIC
  - Jumlah test pass/fail
  - Fix yang dilakukan
  - Sisa debt (jika ada)
  - Rekomendasi untuk EPIC 12 (coverage %)

---

## 8. Risk & Mitigasi

| Risk | Probabilitas | Dampak | Mitigasi |
|------|:------------:|:------:|----------|
| Test fail karena logic bug real (bukan config) | Medium | Medium | Root-cause per test; jangan asumsi "cukup config fix" |
| Refactor CRUDLFIX/Livewire break test EPIC 9 | Medium | Medium | Baca diff commit `064`+`0efdbbc` sebelum update test |
| Migration tidak kompatibel SQLite | Low | Low | Fallback ke MySQL test DB (status quo) — tidak blocking |
| Pindah model/service EPIC 8 break import | Medium | Medium | Skip jika risiko > value; catat sebagai debt |
| Database `sisfokol_laravel_test` konflik dgn sesi lain | Low | Low | Pakai nama unik jika perlu (`sisfokol_laravel_test_<tanggal>`) |

---

## 9. Urutan Task yang Disarankan (Checklist Eksekusi)

```
□ Fase 0: Environment
  □ 0.1 Aktifkan MySQL
  □ 0.2 Buat DB sisfokol_laravel_test
  □ 0.3 Smoke test koneksi
  □ 0.4 Snapshot status awal

□ Fase 1: EPIC 7 Finance
  □ 1.1 Run tests/Feature/Finance → catat hasil
  □ 1.2 Analisis root cause fail
  □ 1.3 Fix iteratif per test class
  □ 1.4 (Opsional) Evaluasi SQLite :memory:
  □ 1.5 (Opsional) Reconcile duplikasi model
  □ 1.6 Catat hasil

□ Fase 2: EPIC 8 Presence
  □ 2.1 Run tests/Feature/Presence → catat hasil
  □ 2.2 Analisis gap coverage
  □ 2.3 Tambah test service kritis
  □ 2.4 (Opsional) Reconcile lokasi model/service
  □ 2.5 Catat hasil

□ Fase 3: EPIC 9 Kurikulum
  □ 3.1 Run KurikulumPluginTest → catat hasil
  □ 3.2 Verifikasi relevansi pasca-refactor
  □ 3.3 Sesuaikan test
  □ 3.4 (Opsional) Tambah subscriber test
  □ 3.5 Catat hasil

□ Fase 4: Final
  □ 4.1 Full Feature suite run
  □ 4.2 Verifikasi DB produksi aman
  □ 4.3 (Opsional) Full project test
  □ 4.4 Final dev report 072
```

---

## 10. Catatan Eksekusi (Skill Pendukung)

Saat eksekusi plan ini, gunakan skill berikut untuk konsistensi metodologi:
- **`smart-debugging`** — saat test fail & perlu investigasi root cause (mulai dari hal sederhana)
- **`systematic-debugging`** — untuk bug tak terduga, sebelum propose fix
- **`test-driven-development`** — saat menambah test baru (Task 2.3, 3.4)
- **`verification-before-completion`** — sebelum claim "Fase X selesai", jalankan command & tunjukkan output

**Prinsip kunci:** Tidak mengklaim PASS tanpa output command sebagai bukti. Setiap klaim selesai didukung evidence.

---

## 11. Output yang Diharapkan

Setelah plan ini dieksekusi:
1. **`072_verifikasi_final_epic_7_8_9_<tanggal>.md`** — dev report final dengan bukti test PASS
2. **Update `069_dev_report_status_epic_lengkap`** — EPIC 7, 8, 9 berubah dari "⚠️ Selesai + catatan" → "✅ Selesai & terverifikasi"
3. **Sisa EPIC yang belum dijalankan** tinggal EPIC 10, 11, 12 (siap untuk plan report berikutnya)

---

*Dokumen ini adalah plan report. Eksekusi dimulai dari Fase 0 — pastikan MySQL aktif sebelum menjalankan test apapun.*
