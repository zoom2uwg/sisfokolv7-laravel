---
inclusion: always
---

# SISFOKOL v7 — Project Context & Rules

**Last Updated:** 2026-06-22 | **Author:** ZCode

## Quick Facts

- **Project:** SISFOKOL v7 — School Management System (SMP/SMA Islam)
- **Stack:** Laravel 11 + MySQL 8 (InnoDB) + Tailwind CSS + Alpine.js
- **Architecture:** Modular Monolith (NOT SPA, NOT API-first)
- **PHP:** Always use `php83` (NOT default `php` 8.1)
- **Composer:** `php83 D:\composer\composer.phar <cmd>`

## Key Decisions (Read Before Coding)

| Decision | Summary |
|----------|---------|
| **UI: Blade SSR + Alpine.js** | NOT Livewire, NOT HTMX. Server renders HTML, Alpine.js for client interactivity. (ADR-011) |
| **API: Fase 2 only** | Do NOT implement API now. Focus on MVC + Blade SSR first. (DEV_DOCS-053) |
| **Multi-tenant:** `BelongsToTenant` trait | Every domain model MUST use this trait for tenant isolation. (ADR-003) |
| **RBAC:** Spatie + teams mode | Database-driven permissions, menu visibility, field ACL. (ADR-006, ADR-010) |
| **Plugins:** `PluginContract` interface | Plug-and-play per tenant via `PluginRegistry`. (ADR-009) |

## Critical Gaps (Known Issues)

1. **Parallel Universe:** `Student`, `Classroom`, `Subject` models do NOT use `BelongsToTenant` and point to English tables (`students`, `classrooms`, `subjects`) instead of Indonesian tables (`siswa`, `kelas`, `mapel`). FIX: Tahap 1.

2. **Event Hook Dead Code:** `EvaluationFrameworkResolver` exists but is never called from `GradeEntryController`. `RaporGeneratorService` never fires `RaportRenderSection`. FIX: Tahap 2.

3. **No .env file:** Database not configured. Tests cannot run. FIX: Create `.env` with MySQL config.

## File Structure

```
sisfokol-laravel/
├── app/Modules/       → 6 core modules (Tenancy, Auth, Academic, Evaluation, Finance, Presence)
├── app/Plugins/       → Plugin system (Kurikulum + Infrastructure)
├── app/Support/       → Cross-cutting (TenantContext, PluginRegistry, FieldAcl, MenuRenderer)
├── app/Models/        → Core models (English naming — Student, Classroom, Subject)
├── resources/views/   → Blade templates + Tailwind CSS
└── ADR/               → Architecture Decision Records (11 files)
```

## What NOT To Do

- Do NOT install Livewire or HTMX (decision: Blade + Alpine.js)
- Do NOT implement API endpoints (decision: Fase 2 after MVP)
- Do NOT create new audit documents (audit phase is closed — DEV_DOCS-052)
- Do NOT claim "done" without physical verification (file exists, test passes)
- Do NOT use `php` — always use `php83`

## Key Documents

| Document | Purpose |
|----------|---------|
| `DEV_DOCS/054` | **MEMORY/HANDOFF** — Read this first |
| `DEV_DOCS/053` | Master implementation plan (tahap eksekusi) |
| `DEV_DOCS/053a` | Physical verification of codebase |
| `DEV_DOCS/053b` | API-Driven MVC verification |
| `DEV_DOCS/053c` | Reusable component library spec |
| `ADR/011` | UI Architecture decision (Blade + Alpine.js) |
