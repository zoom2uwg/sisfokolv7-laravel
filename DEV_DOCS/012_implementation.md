# DEV_DOCS-012: Implementation Plan — Status & Panduan Eksekusi

- **Tanggal:** 2026-06-20 23:25
- **Status:** ✅ EPIC 1 SELESAI — Siap lanjut ke Epic berikutnya
- **Penulis:** Antigravity (Google DeepMind)
- **Untuk:** Semua agentic AI yang mengerjakan proyek ini (Kiro, Antigravity, Opencode, Zcode)
- **Terhubung ke ADR:** 001–010
- **Supersedes:** DEV_DOCS-011 (design phase handover) — file ini adalah update terbaru
- **Plans tersimpan di:** `DOCS/superpowers/plans/` (12 epic plan files)

---

## ⚡ EXECUTIVE SUMMARY

**Design phase: 100% selesai.** Planning phase (epic 1–12): **selesai ditulis**.
**Implementation phase: SEDANG BERJALAN** — Epic 1 (Setup + Fondasi) **SELESAI**.

Proyek: Konversi **SISFOKOL v7 (PHP native, MySQL MyISAM)** → **Laravel 11 modular monolith**.

```
Project root: D:\laragon\www\sisfokolv7\
Laravel app:  D:\laragon\www\sisfokolv7\sisfokol-laravel\   ← LOKASI AKTIF
DB target:    sisfokol_laravel (MySQL, InnoDB)
DB legacy:    sisfokol_v7 (MySQL, MyISAM, read-only)
PHP:          php83 (PHP 8.3.31, WAJIB — bukan `php` default 8.2!)
Composer:     php83 D:\composer\composer.phar <command>
```

---

## 📁 Epic Plans — Sudah Ditulis Lengkap

Semua 12 epic plan tersimpan di `DOCS/superpowers/plans/`:

| File | Epic | Status |
|------|------|--------|
| `2026-06-20-epic-1-setup-fondasi.md` | Setup + Fondasi | ✅ SELESAI |
| `2026-06-20-epic-2-auth-module.md` | Auth Module Full | ✅ SELESAI |
| `2026-06-20-epic-3-rbac-builder.md` | RBAC Builder + Field ACL + Menu Renderer | ✅ SELESAI |
| `2026-06-20-epic-4-plugin-infra.md` | Plugin System Infrastructure | ⏳ pending |
| `2026-06-20-epic-5-academic.md` | Academic Module (11 tables) | ⏳ pending |
| `2026-06-20-epic-6-evaluation.md` | Evaluation Module (7 tables) | ⏳ pending |
| `2026-06-20-epic-7-finance.md` | Finance Module (5 tables, PembayaranService critical) | ⏳ pending |
| `2026-06-20-epic-8-presence.md` | Presence Module (3 tables) | ⏳ pending |
| `2026-06-20-epic-9-kurikulum-plugin.md` | Plugin Kurikulum (referensi penuh) | ⏳ pending |
| `2026-06-20-epic-10-plugin-scaffold.md` | 8 Plugin Scaffold | ⏳ pending |
| `2026-06-20-epic-11-etl-pipeline.md` | ETL Pipeline (20 steps + verify) | ⏳ pending |
| `2026-06-20-epic-12-testing-deployment.md` | Testing + Deployment | ⏳ pending |

---

## 🔄 STATUS EPIC 1 (SEDANG DIKERJAKAN)

### Kondisi saat ini (2026-06-20 22:42 WIB)

**Yang sudah ada di `sisfokol-laravel/`:**
- ✅ Laravel scaffold (artisan, routes/, app/, config/, dll)
- ✅ `storage/`, `bootstrap/`, `public/`, `resources/`
- ✅ `app/Support/` directory dibuat
- ⏳ `vendor/` — **composer install sedang/akan berjalan**
- ❌ Foundation classes belum dibuat
- ❌ Database belum dibuat/migrate

**Masalah yang dihadapi:**
1. **Composer security advisory** — `laravel/framework ^11.54` diblokir oleh advisory composer. Solusi: gunakan `--ignore-platform-req` atau set `"audit": {"ignore": [...]}` di composer.json, ATAU upgrade ke Laravel 12 yang bebas advisory
2. **`lab404/laravel-impersonate ^2.0`** — versi terbaru adalah `1.x`. Gunakan `"^1.7"`
3. **composer.bat** memanggil `php` bukan `php83` — harus panggil langsung: `php83 D:\composer\composer.phar <cmd>`

**Solusi yang direkomendasikan:**
```bash
# Nonaktifkan security advisory blocking (internal project, bukan published package)
# Update composer.json: set "audit": {"blocked-advisories": false} ATAU
# Gunakan Laravel 12 yang bebas dari advisory tersebut
php83 D:\composer\composer.phar install --no-scripts --no-interaction
```

### Task List Epic 1

```
Phase 0: Fix Setup
  [x] Identifikasi lokasi project (sisfokol-laravel/ langsung, bukan nested)
  [/] Install vendor via php83 composer.phar
  [ ] Hapus folder duplikat sisfokol-laravel/sisfokol-laravel/
  [ ] php83 artisan --version → OK

Task 1: Packages
  [ ] Fix composer.json (lab404 ^1.7, audit config, helpers.php autoload)
  [ ] php83 composer install berhasil
  [ ] Git init + first commit

Task 2: Database
  [ ] Buat DB sisfokol_laravel
  [ ] Update .env + config/database.php (dual connection)
  [ ] php83 artisan key:generate

Task 3: Publish vendor config
  [ ] php83 artisan vendor:publish spatie/permission
  [ ] php83 artisan vendor:publish lab404/impersonate
  [ ] config/permission.php → teams=true

Task 4: Foundation classes
  [ ] app/Support/TenantContext.php
  [ ] app/Models/Traits/BelongsToTenant.php
  [ ] app/Models/Traits/TracksAuditColumns.php
  [ ] app/Support/helpers.php (tenant_and_audit_columns, clean_money, clean_date, clean_phone)

Task 5-9: Providers, Middleware, Migrations, Seeders, Migrate
  [ ] ModuleServiceProvider, AppServiceProvider
  [ ] ResolveTenant middleware
  [ ] Migrations (Tenancy 4 + Auth/RBAC 9 + ACL 4 + Plugin infra 2)
  [ ] SuperAdminSeeder, RolePermissionSeeder, MenuSeeder, FieldSeeder
  [ ] php83 artisan migrate --seed
  [ ] php83 artisan test → green
  [ ] git tag epic-1-setup
```

---

## 🏗️ ARSITEKTUR (RINGKAS)

### Stack
- **PHP 8.3** (WAJIB php83, bukan php default 8.2)
- **Laravel 11** (atau 12 bila advisory jadi masalah)
- **MySQL 8 / InnoDB** — database `sisfokol_laravel`
- **Spatie laravel-permission 6.x** — RBAC teams mode
- **lab404/laravel-impersonate 1.x** — Login-As per ADR-005
- **Bootstrap 5 + Alpine.js + Vite** — frontend
- **DomPDF + Laravel Excel + simple-qrcode** — utilities

### Module Structure
```
app/
├── Modules/           ← Core (selalu aktif)
│   ├── Tenancy/
│   ├── Auth/
│   ├── Academic/
│   ├── Evaluation/
│   ├── Finance/
│   └── Presence/
├── Plugins/           ← Plug-and-play per tenant
│   ├── Kurikulum/     ← referensi penuh Fase 1
│   ├── AbsensiGuru/   ← scaffold (Epic 10)
│   ├── Rapor/
│   ├── Spp/
│   ├── Ppdb/
│   ├── Ekstrakurikuler/
│   ├── Bk/
│   ├── Perpustakaan/
│   └── Inventaris/
└── Support/           ← Cross-cutting utilities
    ├── TenantContext.php
    ├── PluginContract.php
    ├── PluginRegistry.php
    ├── FieldAcl.php
    ├── MenuRenderer.php
    └── helpers.php
```

### Database
- **48 tabel** core + **17 tabel** plugin scaffold (Epic 10) + **1 tabel** ETL helper = **66 tabel**
- Semua domain tabel: `tenant_id FK`, `created_by/updated_by`, `deleted_at`
- Uang: `decimal(15,2)` — BUKAN varchar
- PK: `BIGINT UNSIGNED AUTO_INCREMENT` — BUKAN MD5 varchar

---

## ⚠️ ATURAN WAJIB (baca sebelum koding)

Dari `.kiro/steering/karpathy-guidelines.md`:

1. **Backup dulu** sebelum edit file penting → `backups/<tipe>/<nama>.bak_YYYYMMDD`
2. **Verify sebelum execute** perubahan besar > 50 baris
3. **Surgical changes only** — jangan refactor kode yang tidak rusak
4. **Simplicity first** — minimum code, no speculative abstractions

Dari ADR dan design doc:
5. **`BelongsToTenant` wajib** di semua Eloquent model domain — tanpa ini ada risiko data leak antar tenant
6. **`DB::transaction` + `lockForUpdate()` wajib** di PembayaranService — race condition = keuangan rusak
7. **`must_reset_password=true`** untuk semua user hasil ETL
8. **`php83`** untuk semua artisan + composer command — BUKAN `php`
9. **JANGAN implementasi** sebelum test pass
10. **Setiap keputusan penting** → tulis di ADR; setiap sesi panjang → tulis di DEV_DOCS baru

---

## 🔑 KEPUTUSAN BINDING (ADR 001–010)

| # | Keputusan | File ADR |
|---|---|---|
| 1 | ADR pertama selalu dicatat | `ADR/001_*.md` |
| 2 | Rebuild total Laravel 11 modular monolith | `ADR/002_*.md` |
| 3 | Multi-tenant SaaS shared-DB, tenant_id global scope | `ADR/003_*.md` |
| 4 | Scope Fase 1 = 6 core + plugin infra + Kurikulum + ETL | `ADR/004_*.md` |
| 5 | Impersonation hierarkis, env-gated, audit trail | `ADR/005_*.md` |
| 6 | Granular DB-driven RBAC (Spatie teams, resource.aksi) | `ADR/006_*.md` |
| 7 | DB InnoDB 3NF: BIGINT PK, FK, decimal uang, soft delete, audit | `ADR/007_*.md` |
| 8 | DEV_DOCS = memory & handoff antar agent | `ADR/008_*.md` |
| 9 | Plugin system plug-and-play (PluginContract interface) | `ADR/009_*.md` |
| 10 | RBAC sampai menu & field level (database-driven) | `ADR/010_*.md` |

---

## 📝 HANDOFF UNTUK AGENT BERIKUTNYA

### Jika melanjutkan Epic 1

1. Baca file ini (DEV_DOCS-012)
2. Baca `DOCS/superpowers/plans/2026-06-20-epic-1-setup-fondasi.md` (lengkap)
3. Status: **vendor belum terinstall** — mulai dari fix composer.json lalu `php83 D:\composer\composer.phar install`
4. Gunakan `php83` untuk SEMUA perintah PHP, bukan `php`
5. Setelah install berhasil: lanjut Task 2 (DB) → Task 3 (publish) → Task 4 (foundation classes)

### Jika memulai Epic baru (setelah Epic 1 selesai)

1. Baca DEV_DOCS-012 ini untuk status terkini
2. Baca epic plan yang relevan di `DOCS/superpowers/plans/`
3. Baca `sisfokol-laravel/docs/design.md` (sumber kebenaran arsitektur)
4. Cek task list di file ini untuk checkpoint progress
5. Setiap epic selesai → update status tabel di atas + tambah catatan ke sini

### Cara verifikasi setup berhasil

```bash
php83 artisan --version           # → Laravel Framework 11.x.x
php83 artisan migrate:status      # → semua migrations listed
php83 artisan db:seed             # → SuperAdmin + roles + menu seeded
php83 artisan test                # → semua green
# Buka http://sisfokol-laravel.test → halaman login muncul
```

---

## 📞 KONTEKS PROYEK

```
Project root  : D:\laragon\www\sisfokolv7\
Laravel app   : D:\laragon\www\sisfokolv7\sisfokol-laravel\
DB target     : sisfokol_laravel (MySQL, InnoDB, utf8mb4_unicode_ci)
DB legacy     : sisfokol_v7 (MySQL, MyISAM — READ-ONLY, ETL source)
Design doc    : sisfokol-laravel/docs/design.md
Epic plans    : DOCS/superpowers/plans/ (12 files)
ADR           : ADR/ (10 files)
DEV_DOCS      : DEV_DOCS/ (file ini + 11 sebelumnya)
Laragon host  : http://sisfokol-laravel.test (atau http://localhost/sisfokolv7/sisfokol-laravel/public)
```

### Agent yang Bekerja di Proyek Ini
| Agent | Platform | Folder config |
|-------|----------|---------------|
| Kiro | kiro.dev | `.kiro/` (skills, steering, workflows) |
| Antigravity | Google DeepMind | via conversation (menulis file ini) |
| Opencode | — | `.agents/` |
| Zcode | — | `.zcode/` |

**Selalu baca DEV_DOCS terbaru (file dengan nomor tertinggi) sebelum mulai.**
**Selalu tulis DEV_DOCS baru jika sesi panjang / ada keputusan penting.**

---

*File ini ditulis oleh Antigravity, 2026-06-20. Update jika ada progress signifikan.*
