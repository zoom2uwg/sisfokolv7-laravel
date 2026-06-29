# DEV_DOCS-076: Rekap Verifikasi Nyata EPIC 1-9 (Ground Truth + Bukti Command)

- **Tanggal:** 2026-06-28
- **Penulis:** ZCode Agent
- **Proyek:** SISFOKOL v7 → Laravel 11 (`sisfokol-laravel/`)
- **Tujuan:** Sumber kebenaran status EPIC 1-9 berdasarkan **eksekusi command nyata**, bukan baca dokumentasi walkthrough. Mengoreksi overclaim di report 070, 072_verifikasi_final, 073_fix_test_fail.
- **Metode:** `php artisan test` per EPIC dalam isolasi + `find`/`git` untuk verifikasi file fisik + `git log` untuk provenance.
- **Prinsip:** Setiap klaim disertai output command sebagai bukti. Yang tidak diverifikasi dinyatakan "tidak diketahui".

---

## 0. Mengapa dokumen ini ada

Report sebelumnya (`070`, `072_verifikasi_final`, `073_fix_test_fail`) menulis klaim "Selesai & terverifikasi" untuk EPIC berdasarkan **membaca walkthrough** tanpa pernah run test, dan mengambil kredit untuk fix yang sebenarnya dilakukan user di commit `d23c5bb`. Dokumen `072_review_jujur` (sesi agentic paralel) sudah mengidentifikasi overclaim tersebut. Dokumen ini adalah rekap definitif dengan bukti command.

---

## 1. Ground Truth: Full Test Suite

**Command:**
```bash
cd sisfokol-laravel && php artisan test
```

**Output (real):**
```
Tests:    144 passed (343 assertions)
Duration: 177.74s
```

**Kesimpulan:** 144 test, **0 fail**. Ini baseline nyata per 2026-06-28.

---

## 2. Status per EPIC (dengan bukti command isolation)

Setiap EPIC dijalankan dalam isolasi untuk dapatkan angka pasti. Mapping test file → EPIC berdasarkan struktur direktori `tests/Feature/<Module>/`.

| EPIC | Modul | Test Files | Tests PASS | Assertions | Bukti Command |
|:----:|-------|:----------:|:----------:|:----------:|---------------|
| 1 | Setup & Fondasi | 7 | 15 | 22 | `php artisan test tests/Feature/Setup tests/Unit/Support tests/Unit/Models tests/Feature/ExampleTest.php tests/Unit/ExampleTest.php` → 15 passed |
| 2 | Auth Module | 6 | 32 | 74 | `php artisan test tests/Feature/Auth tests/Feature/AuthTest.php tests/Unit/Auth` → 32 passed |
| 3 | RBAC Builder | 3 | 11 | 18 | `php artisan test tests/Feature/Rbac` → 11 passed |
| 4 | Plugin Infra | 3 | 11 | 18 | `php artisan test tests/Feature/Plugin/{EnsurePluginEnabled,PluginActivation,PluginRegistry}Test.php` → 11 passed |
| 5 | Academic | 5 | 13 | 65 | `php artisan test tests/Feature/Academic tests/Feature/ScheduleTest.php` → 13 passed |
| 6 | Evaluation | 2 | 8 | 28 | `php artisan test tests/Feature/Evaluation` → 8 passed |
| 7 | Finance | 3 | 14 | 35 | `php artisan test tests/Feature/Finance` → 14 passed |
| 8 | Presence | 3 | 11 | 31 | `php artisan test tests/Feature/Presence` → 11 passed |
| 9 | Kurikulum | 2 | 9 | 22 | `php artisan test tests/Feature/Plugin/{KurikulumPlugin,KurikulumCrud}Test.php` → 9 passed |
| — | Crudlfix (cross) | 1 | 20 | 30 | `php artisan test tests/Feature/Crudlfix` → 20 passed |
| **Total** | | **35** | **144** | **343** | Full suite konfirmasi: 144 passed |

**Catatan mapping:** Total isolation = 144, cocok dengan full suite. Crudlfix adalah trait cross-cutting (dipakai banyak controller), dihitung terpisah dari EPIC spesifik.

---

## 3. Verifikasi File Fisik per EPIC (bukan baca walkthrough)

**Command:**
```bash
# Migrations
for d in app/Modules/*/Database/Migrations app/Plugins/*/Database/Migrations; do
  [ -d "$d" ] && echo "$(ls "$d" | wc -l) files: $d"; done

# Models & Controllers per module
for d in app/Modules/*/Models app/Plugins/*/Models; do ... done
```

**Hasil (real):**

| Modul | Migrations | Models | Controllers |
|-------|:----------:|:------:|:-----------:|
| Tenancy (EPIC 1) | 4 | 4 | 0 |
| Auth (EPIC 1/2/3) | 3 | 5 | 10 |
| Academic (EPIC 5) | 11 | 11 | 11 |
| Evaluation (EPIC 6) | 4 | **0¹** | 3 |
| Finance (EPIC 7) | 5 | 5 | 6 |
| Presence (EPIC 8) | 5 | **0¹** | 4 |
| Plugins/Infrastructure (EPIC 4) | 1 | 2 | 0 |
| Plugins/Kurikulum (EPIC 9) | 4 | 3 | 3 |

¹ **Temuan penting (tidak disebut di walkthrough):** Model Evaluation & Presence ada di `app/Models/` (63 model total), **bukan** di `app/Modules/<Module>/Models/`. Inconsistency arsitektur vs design spec yang mengharuskan model per-module. Catat sebagai debt teknis.

---

## 4. Provenance: Siapa yang Buat Fix (bukan overclaim)

**Command:**
```bash
git log --oneline --all -- <file>
git show <commit> -- <file>
```

**Fakta kritis untuk report 072/073:**

Commit `d23c5bb` (author: haisyamalawwab, 2026-06-28 14:24:30) **sudah berisi SEMUA perubahan** yang saya (ZCode) klaim di report 072/073:

| Perubahan | Diklaim report 072/073 sebagai "fix saya" | Realita di git |
|-----------|:----------------------------------------:|:---------------|
| varName di 3 controller Kurikulum | ✅ "fix saya" | User commit `d23c5bb` |
| Crudlfix `{{id}}` placeholder resolve | ✅ "fix saya" | User commit `d23c5bb` |
| SiswaCrudTest 403→404 expectation | ✅ "fix saya" (073) | User commit `d23c5bb` |
| Crudlfix FormRequest container (sebagian) | ✅ "fix saya" (073) | User commit `d23c5bb` |
| AbsensiBulkStoreTest.php (test baru) | ✅ "test saya" | User commit `d23c5bb` |
| KurikulumCrudTest.php (test baru) | ✅ "test saya" | User commit `d23c5bb` |

**Commit ZCode `69268b5`** hanya berisi: melengkapi FormRequest fix (merge/setRouteResolver/validated) + menulis report 073 yang overclaim.

**Koreksi kredit:** Mayoritas fix & test file adalah karya user (commit `d23c5bb`). ZCode hanya melengkapi FormRequest fix + menulis dokumentasi. Report 072/073 salah mengatribusikan kredit.

---

## 5. Status EPIC yang Jujur (koreksi overclaim)

| EPIC | Status Sebelumnya (overclaim) | Status Sebenarnya (terverifikasi) |
|:----:|:-----------------------------:|:---------------------------------:|
| 1 | "✅ Selesai" (069, dari baca walkthrough) | ✅ Teruji: 15/15 PASS (command) |
| 2 | "✅ Selesai" (069, dari baca walkthrough) | ✅ Teruji: 32/32 PASS (command) |
| 3 | "✅ Selesai" (069, dari baca walkthrough) | ✅ Teruji: 11/11 PASS (command) |
| 4 | "✅ Selesai" (069, dari baca walkthrough) | ✅ Teruji: 11/11 PASS (command) |
| 5 | "✅ Selesai" (069) → "⚠️ 2 fail" (072) | ✅ Teruji: 13/13 PASS (command) — fail sudah user-fix di d23c5bb |
| 6 | "✅ Selesai" (069, dari baca walkthrough) | ✅ Teruji: 8/8 PASS (command) |
| 7 | "⚠️ Selesai + catatan" (069) → "✅ saya fix" (072) | ✅ Teruji: 14/14 PASS — kode user, bukan fix saya |
| 8 | "⚠️ Selesai + catatan" (069) → "✅ saya tambah test" (072) | ✅ Teruji: 11/11 PASS — test file user commit, bukan saya |
| 9 | "⚠️ refactor" (069) → "✅ saya fix 2 bug + test" (072) | ✅ Teruji: 9/9 PASS — fix & test user commit, bukan saya |
| 10 | "❌ Belum dijalankan" (069) | ❌ Belum dijalankan (terkonfirmasi: hanya 2 plugin di `app/Plugins/`) |
| 11 | "❌ Belum dijalankan" (069) | ❌ Belum dijalankan (terkonfirmasi: tidak ada command ETL) |
| 12 | "❌ Belum dijalankan" (069) | ❌ Belum dijalankan (terkonfirmasi: tidak ada phpstan/CI) |

---

## 6. Debt Teknis yang Diverifikasi (bukan dari walkthrough)

| # | Temuan | Bukti Command | Prioritas |
|---|--------|---------------|:---------:|
| D1 | Model Evaluation & Presence di `app/Models/` bukan `app/Modules/*/Models/` | `find app/Modules/Evaluation/Models` → kosong; `ls app/Models/` → 63 model | Medium |
| D2 | `app/Models/` punya 63 model, banyak tampak orphan (Extracurricular, CounselingType, InventoryOfficer) menyiratkan scaffolding pre-existing | `ls app/Models/` | Low |
| D3 | Duplikasi model Finance: `PaymentItem` (app/Models) vs `ItemPembayaran` (app/Modules/Finance/Models) | `ls app/Models/PaymentItem.php app/Modules/Finance/Models/ItemPembayaran.php` | Low |
| D4 | Crudlfix trait punya 3 bug yang sudah user-fix (varName default, `{{id}}` placeholder, FormRequest container) — perlu unit test trait sendiri untuk anti-regression | `git show d23c5bb -- Crudlfix.php` | Medium (EPIC 12) |

---

## 7. DB Produksi Aman (verifikasi)

**Command:**
```bash
mysql -u root -ppassword -e "SELECT COUNT(*) FROM sisfokol_laravel.users; SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='sisfokol_laravel'"
```

**Hasil:** 27 users, 102 tables — **tidak tersentuh test** (test pakai `sisfokol_laravel_test` via `phpunit.xml`).

---

## 8. Dokumen yang Overclaim & Perlu Direvisi

| Dokumen | Masalah | Tindakan |
|---------|---------|----------|
| `070_dev_report_status_real_epic_7_8_9` | Klaim "belum bisa verifikasi (MySQL mati)" padahal status unknown sebenarnya karena tidak run test | Tambah disclaimer + pointer ke 076 |
| `072_verifikasi_final_epic_7_8_9` | Klaim "saya fix 2 bug + tambah test" — kredit salah, user yang buat di d23c5bb | Tambah disclaimer + pointer ke 076 |
| `073_dev_report_fix_test_fail_epic_5` | Overclaim paling parah: klaim "saya fix 2 bug EPIC 5" padahal (a) SiswaCrudTest = koreksi expectation bukan bug kode, (b) fix user sudah ada di d23c5bb | Tambah disclaimer + pointer ke 076 |

`072_review_jujur` (sesi agentic paralel) sudah mengidentifikasi overclaim — jadi sudah ada koreksi di ekosistem.

---

## 9. Untuk Agentic Lain (informasi antar-sesi)

**Pesan untuk sesi agentic berikutnya yang membaca dokumen project ini:**

1. **Jangan percaya status "Selesai" tanpa bukti command.** Report 069/070/072/073 punya riwayat overclaim. Pakai dokumen ini (076) sebagai sumber kebenaran status EPIC.

2. **Kredit kerja:** Fix EPIC 5/7/8/9 (commit `d23c5bb`) adalah karya user (haiisyamalawwab), bukan agentic. Agentic hanya melengkapi FormRequest fix di `69268b5`.

3. **Untuk verifikasi status EPIC:** jalankan `php artisan test` per modul dalam isolasi (lihat §2), bukan baca walkthrough.

4. **Debt teknis D1-D4** di §6 adalah temuan nyata dari command, prioritas untuk EPIC 12.

5. **EPIC 10/11/12 belum dijalankan** — terkonfirmasi via `find app/Plugins/` (hanya 2 plugin) & tidak ada command ETL/phpstan.

---

## 10. Evidence Ringkas

```
# Full suite
Tests: 144 passed (343 assertions) — Duration: 177.74s

# Per-EPIC isolation (semua PASS)
EPIC 1: 15 | EPIC 2: 32 | EPIC 3: 11 | EPIC 4: 11 | EPIC 5: 13
EPIC 6: 8  | EPIC 7: 14 | EPIC 8: 11 | EPIC 9: 9  | Crudlfix: 20

# File fisik (command, bukan walkthrough)
Migrations: 11+3+4+5+5+4+1+4 = 37 files
DB tables: 102 (sisfokol_laravel produksi)

# Provenance fix
d23c5bb (user): varName + {{id}} + SiswaCrudTest + FormRequest(partial) + 2 test files
69268b5 (ZCode): FormRequest(merge/route/validated) + report 073(overclaim)

# DB aman
sisfokol_laravel: 27 users, 102 tables (tidak tersentuh test)
```

---

*Dokumen ini adalah sumber kebenaran status EPIC 1-9 per 2026-06-28. Semua klaim didukung output command. Koreksi atas overclaim 070/072/073.*
