# DEV_DOCS-054: MEMORY & HANDOFF — Konteks Terkini untuk Agentic AI

- **Tanggal:** 2026-06-22
- **Status:** 📋 ACTIVE MEMORY — Baca ini SEBELUM mulai bekerja
- **Penulis:** ZCode
- **Tujuan:** Dokumen ini adalah SATU-SATUNYA sumber kebenaran terkini. Baca ini duluan.

---

## ⚡ BACA INI DULU

Kamu adalah agentic AI yang baru memulai sesi di proyek **SISFOKOL v7**. Dokumen ini berisi semua yang perlu kamu ketahui. **JANGAN langsung coding** — baca dokumen ini sampai selesai, pahami konteksnya, baru eksekusi.

---

## 1. PROYEK INI APA?

**SISFOKOL v7** = Konversi aplikasi sekolah Islam dari **PHP native (MyISAM)** ke **Laravel 11 modular monolith (InnoDB)**.

```
Project root  : D:\laragon\www\sisfokolv7\
Laravel app   : D:\laragon\www\sisfokolv7\sisfokol-laravel\
DB target     : sisfokol_laravel (MySQL, InnoDB, utf8mb4_unicode_ci)
DB legacy     : sisfokol_v7 (MySQL, MyISAM — READ-ONLY, ETL source)
PHP           : php83 (PHP 8.3.31, WAJIB — bukan `php` default 8.2!)
Composer      : php83 D:\composer\composer.phar <command>
Host          : http://sisfokol-laravel.test
```

**⚠️ PENTING:** Selalu gunakan `php83` untuk SEMUA artisan & composer command. Jangan `php` default (8.1).

---

## 2. STATUS TERKINI (2026-06-22)

### Apa yang SUDAH SELESAI (verified di disk):

| Komponen | Status | Bukti |
|----------|--------|-------|
| Laravel scaffold | ✅ 100% | `artisan`, routes, app, config |
| Multi-tenant trait | ✅ 100% | `BelongsToTenant.php` → 14 model pakai |
| Audit logging | ✅ 100% | `AuditLogger.php` + 3 Observer |
| RBAC Spatie | ✅ 100% | Dynamic menu, field ACL, blade directives |
| Auth module | ✅ 95% | Login, impersonation, force password reset |
| Plugin infrastructure | ✅ 85% | Registry, activation, middleware |
| Academic module | ✅ 85% | 11 tabel, CRUD, conflict checker, promotion |
| Evaluation module | ✅ 55% | Controllers ada, tapi event hook tidak dipanggil |
| Finance module | ✅ 65% | PembayaranService dengan lockForUpdate |
| Presence module | ✅ 70% | QR scanner, approval workflow |
| Plugin Kurikulum | ✅ 30% | Dead code — subscriber tidak terpanggil |

### Apa yang BELUM SELESAI (critical gaps):

| Gap | Severity | Detail |
|-----|----------|--------|
| **Parallel Universe** | 🔴 KRITIS | `Student`/`Classroom`/`Subject` model TIDAK pakai `BelongsToTenant` dan TIDAK pointing ke tabel modular (`siswa`/`kelas`/`mapel`) |
| **Event Hook Dead Code** | 🔴 KRITIS | `EvaluationFrameworkResolver` ada tapi tidak dipanggil dari controller. `RaporGeneratorService` tidak fire `RaportRenderSection` |
| **API = 0%** | 🟡 DITUNDA | `routes/api.php` tidak di-load, Sanctum tidak terpasang. **DIPUTUSKAN: API di Fase 2** |
| **Plugin Scaffold** | 🟡 RENDAH | 7 plugin (AbsensiGuru, Rapor, Spp, Ppdb, Ekstrakurikuler, Bk, Perpustakaan, Inventaris) TIDAK ADA |
| **ETL Pipeline** | 🟡 RENDAH | `MigrateLegacyDataCommand` tidak ada |
| **.env file** | 🔴 KRITIS | Tidak ada `.env` → tests tidak bisa dijalankan |

### Apa yang TIDAK ADA di disk:

- `.env` file (perlu dibuat dengan konfigurasi MySQL)
- `app/Http/Resources/` directory
- `config/cors.php`
- `config/sanctum.php`
- 7 plugin scaffold
- ETL command

---

## 3. KEPUTUSAN ARSITEKTUR (ADR)

Baca file ADR di folder `ADR/` untuk keputusan lengkap. Ringkasan:

| ADR | Keputusan | Status |
|-----|-----------|--------|
| ADR-001 | Selalu catat ADR | ✅ Aktif |
| ADR-002 | Rebuild total Laravel 11 modular monolith | ✅ Aktif |
| ADR-003 | Multi-tenant SaaS shared-DB, `tenant_id` global scope | ✅ Aktif |
| ADR-004 | Scope Fase 1 = 6 core + plugin infra + Kurikulum + ETL | ✅ Aktif |
| ADR-005 | Impersonation hierarkis, env-gated, audit trail | ✅ Aktif |
| ADR-006 | Granular DB-driven RBAC (Spatie teams, resource.aksi) | ✅ Aktif |
| ADR-007 | DB InnoDB 3NF: BIGINT PK, FK, decimal uang, soft delete, audit | ✅ Aktif |
| ADR-008 | DEV_DOCS = memory & handoff antar agent | ✅ Aktif |
| ADR-009 | Plugin system plug-and-play (PluginContract interface) | ✅ Aktif |
| ADR-010 | RBAC sampai menu & field level (database-driven) | ✅ Aktif |
| **ADR-011** | **UI Architecture: Blade SSR + Alpine.js + Tailwind (BUKAN Livewire)** | ✅ **BARU** |

### Keputusan UI/UX (ADR-011 — BARU):

```
FASE 1 (MVP): Blade SSR + Alpine.js + Tailwind CSS
  • Server render HTML sekali, selesai (paling ringan)
  • Alpine.js untuk interaktivitas client-side
  • Tidak ada dependency baru (TIDAK Livewire, TIDAK HTMX)
  • API: TIDAK ADA di fase ini

FASE 2 (SETAP MVP SELESAI): + REST API (Sanctum)
  • Hanya di titik khusus: Login, Scan, Nilai, Tagihan, Jadwal
```

---

## 4. IMPLEMENTATION PLAN

Baca `DEV_DOCS/053_master_implementation_plan_konsolidasi_20260622.md` untuk detail.

### Tahap Eksekusi:

```
TAHAP 1: Unifikasi Model & Database [KRITIS]     ← PRIORITAS UTAMA
TAHAP 2: Aktivasi Event Hook & Fix Crash [KRITIS]
TAHAP 3: Konsolidasi Finance & Presence [TINGGI]
TAHAP 4: UI Component Library [SEDIANG]           ← BARU (ADR-011)
TAHAP 5: Plugin Scaffold & ETL [RENDAH]
TAHAP 6: Testing & Deployment [AKHIR]
```

**⚠️ MULAI DARI TAHAP 1.** Jangan lompat ke tahap lain sebelum Tahap 1 selesai.

---

## 5. DOKUMEN PENTING (Baca Sebelum Eksekusi)

### Wajib Baca:
| Dokumen | Isi |
|---------|-----|
| **DEV_DOCS-054** | 👈 DOKUMEN INI — memory/handoff |
| **DEV_DOCS-053** | Master implementation plan (tahap eksekusi) |
| **DEV_DOCS-053a** | Verifikasi fisik codebase Epic 1-9 |
| **DEV_DOCS-053b** | Verifikasi API-Driven MVC Epic 1-6 |
| **DEV_DOCS-053c** | Reusable component library spec |
| **ADR-011** | Keputusan UI Architecture |

### Referensi (baca jika perlu konteks):
| Dokumen | Isi |
|---------|-----|
| DEV_DOCS-012 | Master implementation plan asli (Antigravity) |
| DEV_DOCS-013 s.d. 030 | Walkthrough & dev report per epic |
| DEV_DOCS-043 s.d. 052 | Audit & gap analysis |
| ADR-001 s.d. 010 | Keputusan arsitektur |

### Jangan Baca (audit sudah ditutup):
- DEV_DOCS-044 (berbagai versi) — audit post-hoc, overlapping
- DEV_DOCS-048, 049 — review dokumentasi

---

## 6. ARSITEKTUR APLIKASI

### Module Structure:
```
app/
├── Modules/           ← Core (selalu aktif)
│   ├── Tenancy/       → Tenant, Branch, Subscription
│   ├── Auth/          → Login, RBAC, Audit, Plugin Mgmt
│   ├── Academic/      → Siswa, Guru, Kelas, Jadwal
│   ├── Evaluation/    → Nilai, Rapor, Kurikulum
│   ├── Finance/       → Pembayaran, Tagihan, Tabungan
│   └── Presence/      → Presensi, Izin, QR Scanner
│
├── Plugins/           ← Plug-and-play per tenant
│   ├── Infrastructure/ → Plugin & TenantPlugin models
│   └── Kurikulum/     → CRUD Kurikulum, Subscriber
│
├── Support/           ← Cross-cutting utilities
│   ├── TenantContext.php
│   ├── PluginContract.php
│   ├── PluginRegistry.php
│   ├── FieldAcl.php
│   ├── MenuRenderer.php
│   └── helpers.php
│
├── Models/            ← Core models (ENGLISH naming)
│   ├── Student.php    → ⚠️ TIDAK pakai BelongsToTenant
│   ├── Classroom.php  → ⚠️ TIDAK pakai BelongsToTenant
│   ├── Subject.php    → ⚠️ TIDAK pakai BelongsToTenant
│   └── ...            → 14 model lain SUDAH pakai BelongsToTenant
│
└── Http/
    ├── Controllers/
    │   ├── Api/       → 2 controller (AuthController, ScheduleController)
    │   ├── Admin/     → 9 controllers (CRUD core)
    │   ├── Teacher/   → 5 controllers
    │   └── ...        → 8 role-based controllers
    └── Middleware/
        ├── ResolveTenant.php
        ├── ForcePasswordReset.php
        ├── BlockWhileImpersonating.php
        └── EnsurePluginEnabled.php
```

### Database (Dual Universe — MASIH ADA):
```
TABEL MODULAR (Bahasa Indonesia) → Dipakai modul:
  siswa, kelas, mapel, tahun_ajaran, semester, guru, jadwal, dll

TABEL CORE (Bahasa Inggris) → Dipakai model core:
  students, classrooms, subjects, academic_years, dll

⚠️ KEDUANYA TIDAK TERHUBUNG → INI MASALAH UTAMA
```

### Routes:
```
routes/web.php        → ~155 baris (role-based routes)
routes/api.php        → 21 baris (TIDAK DI-LOAD oleh bootstrap/app.php)
Modules/*/routes.php  → Per-modul routes (auto-loaded via ModuleServiceProvider)
Plugins/*/routes.php  → Per-plugin routes (middleware plugin:kurikulum)
```

---

## 7. FILE KRITIS YANG PERLU DIPERHATIKAN

### Jangan Diubah Tanpa Alasan:
```
app/Support/TenantContext.php          → Fondasi multi-tenancy
app/Models/Traits/BelongsToTenant.php → Global scope tenant
app/Models/Traits/TracksAuditColumns.php → Audit trail
bootstrap/app.php                      → Middleware & routing config
config/modules.php                     → Module registration
```

### Perlu Diubah (TAHAP 1):
```
app/Models/Student.php       → TAMBAH BelongsToTenant + $table = 'siswa'
app/Models/Classroom.php     → TAMBAH BelongsToTenant + $table = 'kelas'
app/Models/Subject.php       → TAMBAH BelongsToTenant + $table = 'mapel'
app/Models/AcademicYear.php  → TAMBAH BelongsToTenant + $table = 'tahun_ajaran'
```

### Perlu Diubah (TAHAP 2):
```
app/Modules/Evaluation/Controllers/GradeEntryController.php → PANGGIL EvaluationFrameworkResolver
app/Modules/Evaluation/Services/RaporGeneratorService.php   → FIRE RaportRenderSection
```

### Perlu Dibuat (TAHAP 4):
```
resources/views/components/ui/*.blade.php    → 7 UI components
resources/views/components/form/*.blade.php  → 5 form components
resources/views/components/table/*.blade.php → 3 table components
resources/views/partials/*.blade.php         → 3 shared partials
```

---

## 8. CARA KERJA YANG BENAR

### Sebelum Mulai:
1. ✅ Baca dokumen ini (DEV_DOCS-054)
2. ✅ Baca DEV_DOCS-053 (implementation plan)
3. ✅ Baca ADR-011 (UI architecture)
4. ✅ Verifikasi file yang akan diubah ada di disk

### Saat Bekerja:
1. ✅ `php83` untuk SEMUA artisan & composer command
2. ✅ Backup dulu sebelum edit file penting → `backups/<tipe>/<nama>.bak_YYYYMMDD`
3. ✅ Surgical changes only — jangan refactor yang tidak rusak
4. ✅ Test setelah setiap perubahan signifikan
5. ✅ Centang task di implementation plan

### Setelah Selesai:
1. ✅ Update status di DEV_DOCS-053
2. ✅ Tulis DEV_DOCS baru jika ada keputusan penting
3. ✅ Commit dengan pesan yang jelas

### JANGAN LAKUKAN:
- ❌ Jangan buat dokumen audit baru (fase audit sudah ditutup — DEV_DOCS-052)
- ❌ Jangan implementasi API (belum saatnya — Fase 2)
- ❌ Jangan install Livewire/HTMX (keputusan: Blade + Alpine.js — ADR-011)
- ❌ Jangan klaim "selesai" tanpa bukti fisik (file ada, test pass)
- ❌ Jangan overclaim — verifikasi dulu sebelum klaim

---

## 9. POLA YANG HARUS DIHINDARI

### Pola "Ilusi Penyelesaian" (sudah terjadi sebelumnya):
```
1. Agent buat dev report klaim "✅ SELESAI"
2. Agent lain audit → temukan overclaim
3. Lalu dibuat recovery plan baru
4. Loop kembali ke step 1
```

**Cara hindari:**
- Verifikasi fisik SEBELUM klaim selesai
- Jalankan test SEBELUM klaim green
- Cek file ada di disk SEBELUM klaim implemented
- Tulis bukti konkret (file path, line number, artisan output)

---

## 10. KONTEKS TENANT & USER

### Tenant Demo:
```
Tenant: SMA Demo Sisfokol (NPSN: 20000001)
Siswa: 20 (NIS: 2024001 – 2024020)
Kelas: 7 (X IPA 1, X IPA 2, X IPS 1, XI IPA 1, XI IPS 1, XII IPA 1, XII IPS 1)
```

### Akun Demo (jika database terisi):
| Role | Username | Password |
|------|----------|----------|
| SuperAdmin | `superadmin` | `SuperAdmin#2026` |
| Admin Sekolah | `admin.sekolah` | `demo1234` |
| Guru Piket | `piket.demo` | `demo1234` |
| Guru BK | `bk.demo` | `demo1234` |
| Guru Mapel | `guru.demo` | `demo1234` |
| Wali Kelas | `walikelas.demo` | `demo1234` |
| Siswa | `siswa.2024001` | `demo1234` |

**⚠️ CATATAN:** Akun demo hanya ada jika database sudah di-seed. Saat ini `.env` tidak ada → database tidak terkonfigurasi.

---

## 11. AGENT YANG BEKERJA DI PROYEK INI

| Agent | Platform | Folder config |
|-------|----------|---------------|
| Kiro | kiro.dev | `.kiro/` (skills, steering, workflows) |
| Antigravity | Google DeepMind | via conversation (menulis DEV_DOCS 012-030) |
| Opencode | — | `.agents/` |
| ZCode | — | `.zcode/` |

**Tips:** Setiap agent punya gaya kerja berbeda. Jangan asumsikan dokumen sebelumnya 100% akurat — **selalu verifikasi fisik**.

---

## 12. QUICK START UNTUK AGENT BARU

```bash
# 1. Baca memory ini
# (kamu sudah di sini ✅)

# 2. Baca implementation plan
cat DEV_DOCS/053_master_implementation_plan_konsolidasi_20260622.md

# 3. Baca ADR terbaru
cat ADR/011_ui_architecture_blade_alpine_ssr_20260622.md

# 4. Verifikasi kondisi codebase
ls sisfokol-laravel/app/Models/Student.php
grep "BelongsToTenant" sisfokol-laravel/app/Models/Student.php
# → Jika tidak ada output = CONFIRMED gap, mulai Tahap 1

# 5. Mulai eksekusi Tahap 1
# (lihat DEV_DOCS-053 untuk detail task)
```

---

## 📞 KONTAK & LINK

```
Project root  : D:\laragon\www\sisfokolv7\
Laravel app   : D:\laragon\www\sisfokolv7\sisfokol-laravel\
DB target     : sisfokol_laravel (MySQL, InnoDB)
DB legacy     : sisfokol_v7 (MySQL, MyISAM — READ-ONLY)
Design doc    : sisfokol-laravel/docs/design.md
Epic plans    : DOCS/superpowers/plans/ (12 files)
ADR           : ADR/ (11 files)
DEV_DOCS      : DEV_DOCS/ (54+ files)
```

---

## 🔄 UPDATE LOG

| Tanggal | Perubahan | Oleh |
|---------|-----------|------|
| 2026-06-20 | Initial setup, Epic 1-3 selesai | Antigravity |
| 2026-06-21 | Epic 4-9 diklaim selesai | Antigravity |
| 2026-06-22 | Audit DEV_DOCS 030-052, temukan overclaim | ZCode |
| 2026-06-22 | Verifikasi fisik codebase (DEV_DOCS-053a) | ZCode |
| 2026-06-22 | Verifikasi API-Driven MVC (DEV_DOCS-053b) | ZCode |
| 2026-06-22 | ADR-011: Blade + Alpine.js (BUKAN Livewire) | ZCode |
| 2026-06-22 | Component library spec (DEV_DOCS-053c) | ZCode |
| 2026-06-22 | Memory & Handoff doc (DEV_DOCS-054) ← INI | ZCode |

---

*Dokumen ini adalah SATU-SATUNYA sumber kebenaran terkini.*
*Jika ada konflik antara dokumen ini dan dokumen lain, DOKUMEN INI YANG MENANG.*
*Baca ini duluan, baru baca dokumen lain.*
