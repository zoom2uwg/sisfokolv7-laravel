---
inclusion: always
---

# SISFOKOL Project Context — Agent Steering

> **Baca ini PERTAMA sebelum mulai bekerja di proyek ini.**
> Berlaku untuk semua agent: Antigravity, Kiro, Opencode, Zcode.
> **Last Updated:** 2026-06-22 (oleh ZCode)

## Identitas Proyek

```
Nama       : SISFOKOL Laravel 11
Tipe       : Domain-Modular Monolith (bukan microservices, bukan SPA)
Goal       : Konversi SISFOKOL v7 (PHP native) → Laravel 11
Root       : D:\laragon\www\sisfokolv7\
Laravel app: D:\laragon\www\sisfokolv7\sisfokol-laravel\
DB target  : sisfokol_laravel (MySQL 8, InnoDB)
DB legacy  : sisfokol_v7 (MySQL, MyISAM, READ-ONLY — ETL source)
```

## PHP Execution Rule (KRITIS)

```powershell
# SELALU gunakan php83, BUKAN php
php83 artisan <command>
php83 D:\composer\composer.phar <command>

# php default = 8.1.x → GAGAL (requirement >= 8.2)
# php83 = 8.3.31 → OK
```

## ⚡ STATUS TERKINI (2026-06-22)

### Memory Document (BACA INI DULU):
```
DEV_DOCS/054_memory_handoff_konteks_terkini_20260622.md  ← SATU-SATUNYA SUMBENAR
```

### Implementation Plan:
```
DEV_DOCS/053_master_implementation_plan_konsolidasi_20260622.md
```

### Tahap Eksekusi:
```
TAHAP 1: Unifikasi Model & Database [KRITIS]     ← MULAI DARI SINI
TAHAP 2: Aktivasi Event Hook & Fix Crash [KRITIS]
TAHAP 3: Konsolidasi Finance & Presence [TINGGI]
TAHAP 4: UI Component Library [SEDIANG]
TAHAP 5: Plugin Scaffold & ETL [RENDAH]
TAHAP 6: Testing & Deployment [AKHIR]
```

## Keputusan Arsitektur (ADR)

| ADR | Keputusan | Status |
|-----|-----------|--------|
| ADR-003 | Multi-tenant SaaS shared-DB, `tenant_id` global scope | ✅ Aktif |
| ADR-006 | Granular DB-driven RBAC (Spatie teams) | ✅ Aktif |
| ADR-009 | Plugin system plug-and-play | ✅ Aktif |
| ADR-010 | RBAC sampai menu & field level | ✅ Aktif |
| **ADR-011** | **UI: Blade SSR + Alpine.js (BUKAN Livewire, BUKAN HTMX)** | ✅ **BARU** |

### UI Architecture (ADR-011):
```
FASE 1 (MVP): Blade SSR + Alpine.js + Tailwind CSS
  • Server render HTML sekali, selesai (paling ringan)
  • Alpine.js untuk interaktivitas client-side
  • Tidak ada dependency baru
  • API: TIDAK ADA di fase ini

FASE 2 (SETAP MVP): + REST API (Sanctum) — hanya di titik khusus
```

### API Decision (DEV_DOCS-053b):
```
API-Driven MVC = TIDAK ADA saat ini (~1.5/10)
routes/api.php TIDAK DI-LOAD oleh bootstrap/app.php
Sanctum TIDAK TERPASANG
API akan diimplementasi di Fase 2 SETAP MVP selesai
```

## Critical Gaps (Known Issues)

| Gap | Severity | Fix di |
|-----|----------|--------|
| `Student`/`Classroom`/`Subject` TIDAK pakai `BelongsToTenant` | 🔴 KRITIS | Tahap 1 |
| Event hook `EvaluationFrameworkResolver` tidak dipanggil | 🔴 KRITIS | Tahap 2 |
| `RaporGeneratorService` tidak fire `RaportRenderSection` | 🔴 KRITIS | Tahap 2 |
| `.env` file tidak ada → tests tidak bisa jalan | 🔴 KRITIS | Tahap 1 |
| 7 plugin scaffold tidak ada | 🟡 RENDAH | Tahap 5 |
| ETL command tidak ada | 🟡 RENDAH | Tahap 5 |

## What NOT To Do

- ❌ Jangan install Livewire atau HTMX (keputusan: Blade + Alpine.js — ADR-011)
- ❌ Jangan implementasi API (keputusan: Fase 2 — DEV_DOCS-053)
- ❌ Jangan buat dokumen audit baru (audit phase ditutup — DEV_DOCS-052)
- ❌ Jangan klaim "selesai" tanpa verifikasi fisik
- ❌ Jangan gunakan `php` — selalu `php83`

## Aturan Wajib (dari Karpathy Guidelines)

1. **Backup sebelum edit** → `backups/<tipe>/<nama>.bak_YYYYMMDD`
2. **Verify sebelum execute** perubahan > 50 baris
3. **Jangan refactor** kode yang tidak rusak
4. **Simplicity first** — minimum code
5. **BelongsToTenant trait wajib** di semua model domain
6. **DB::transaction + lockForUpdate()** di PembayaranService
7. **Tulis DEV_DOCS baru** setiap sesi panjang / keputusan penting

## Key Documents

| Document | Purpose |
|----------|---------|
| `DEV_DOCS/054` | **MEMORY/HANDOFF** — Baca ini pertama |
| `DEV_DOCS/053` | Master implementation plan |
| `DEV_DOCS/053a` | Verifikasi fisik codebase |
| `DEV_DOCS/053b` | Verifikasi API-Driven MVC |
| `DEV_DOCS/053c` | Reusable component library spec |
| `ADR/011` | UI Architecture decision |

## Agent Folders

| Agent | Folder |
|-------|--------|
| Kiro (kiro.dev) | `.kiro/` (skills, steering, workflows) |
| Antigravity (Google DeepMind) | `.agents/` (steering, skills, workflows) |
| Opencode | `.agents/` (shared) |
| Zcode | `.zcode/` |
