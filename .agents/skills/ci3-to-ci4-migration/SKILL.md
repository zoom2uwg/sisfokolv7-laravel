---
name: ci3-to-ci4-migration
description: >
  Panduan + helper scripts untuk migrasi project CodeIgniter 3 (CI3) ke CodeIgniter 4 (CI4)
  dengan feature-parity. Gunakan skill ini setiap kali user menyebut konversi/migrasi/upgrade
  CodeIgniter 3 ke 4, pindah aplikasi CI3 ke CI4, atau minta bantuan convert controller/model/routing
  CI3 ke sintaks CI4 — bahkan kalau user tidak eksplisit bilang "migrasi".
---

# CI3 → CI4 Migration (Hybrid: Guide + Scripts)

Skill ini memandu migrasi CodeIgniter 3 (CI3) ke CodeIgniter 4 (CI4) dengan target **feature parity** — bukan sekadar "jalan tanpa error", tapi semua fitur CI3 berfungsi sama di CI4. Pendekatan hybrid: bagian mekanis (regex aman) via scripts, bagian judgment via panduan reference.

## Kapan skill ini dipakai

Trigger: user menyebut konversi/migrasi/upgrade CI3 ke CI4, pindah aplikasi CI3 ke CI4, atau minta convert controller/model/routing CI3 ke sintaks CI4.

## Workflow inti (7 langkah)

1. **Audit CI3 source**
   - Baca `references/00-audit-checklist.md`
   - Jalankan: `node scripts/audit-ci3.mjs <ci3-application-path>`
   - Output: laporan pola CI3 + impact analysis (dependency antar komponen) + estimasi effort

2. **Cek keberadaan project CI4**
   - Belum ada → setup via `references/01-bootstrap-config.md`
   - Sudah ada → lanjut step 3

3. **Pilih strategi urutan** (decision tree di bawah)

4. **Konversi per area** — WAJIB baca reference area sebelum handle

5. **Jalankan scripts mekanis** untuk bagian aman (selalu `--dry-run` dulu, review, baru `--apply`)

6. **Feature-parity check** via `assets/feature-parity-checklist.md` + `node scripts/feature-parity-check.mjs <ci3> <ci4>`, lalu **code-quality gate** via `assets/output-quality-checklist.md` (sekunder)

7. **Testing per fitur** (input/output/edge-case sama dengan CI3). Claim selesai HANYA setelah feature parity terpenuhi.

## Decision tree — urutan konversi

- Project kecil (<10 controller, sedikit custom library) → **layer-by-layer** OK
- Project besar / banyak modul / banyak custom library → **incremental per-modul** (testable, rollback mudah)
- Ada `MY_Controller`/`MY_Model` kritis → konversi dulu sebelum controller lain (banyak controller bergantung)
- Hosting constrain PHP version → cek `references/10-php-modernization.md` lebih awal
- Ada REST API controller → baca `references/03-controllers.md` (section ResourceController)

## Router reference (baca sebelum handle area tsb)

| Area | Reference | Mekanis? (script) |
|------|-----------|-------------------|
| Audit | `00-audit-checklist.md` | `audit-ci3.mjs` |
| Bootstrap/Config | `01-bootstrap-config.md` | - |
| Routing | `02-routing.md` | `convert-mechanical.mjs` (sebagian) |
| Controller | `03-controllers.md` | `convert-mechanical.mjs` |
| Model/DB | `04-models-db.md` | `rename-files.mjs` + `convert-mechanical.mjs` |
| View | `05-views.md` | `convert-mechanical.mjs` |
| Library/Helper | `06-libraries-helpers.md` | `rename-files.mjs` |
| Services | `07-services.md` | `convert-mechanical.mjs` |
| Hooks/Events | `08-hooks-events-filters.md` | - (judgment) |
| Third-party | `09-third-party.md` | - (judgment) |
| PHP modernisasi | `10-php-modernization.md` | - (judgment) |
| Migration/Seeder/Security/Pagination | `11-migration-seeder-security.md` | - (judgment) |

Untuk lookup cepat saat debug area tertentu, baca `assets/mapping-table.md` (superset semua mapping).

## Prinsip wajib

- **COMMENT jangan DELETE** — kode CI3 lama di-comment dengan label `[YYYY-MM-DD | agent]`, bukan dihapus. Rollback = uncomment. Contoh:
  ```php
  // [2026-06-29 | ci3-to-ci4] ganti ke CI4 request
  // $x = $this->input->post('x');
  $x = $this->request->getPost('x');
  ```
- **Mekanis via script, judgment via manual** — JANGAN regex-sendiri bagian berisiko (`extends CI_*`, `$this->load->model/library`, business logic, hooks→events, migration/seeder, security rewrite)
- **Script selalu `--dry-run` dulu** — review diff, baru `--apply`. Tidak ada perubahan file tanpa review.
- **Feature-parity WAJIB** sebelum claim selesai (def of done = feature parity, bukan "jalan tanpa error")
- **Verifikasi post-konversi per file** — baris 1 `<?php`, ada namespace, extends base yang benar
- **Code-quality gate sekunder** — setelah feature parity, jalankan `assets/output-quality-checklist.md`. Type declaration boleh jadi debt (jangan campur dengan konversi struktural)
