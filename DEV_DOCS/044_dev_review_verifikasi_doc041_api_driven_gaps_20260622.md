# DEV_DOCS-044: Dev Review — Verifikasi Implementasi Rekomendasi DOC-041 (API-Driven Gaps)

- **Tanggal**: 2026-06-22
- **Verifikator**: ZCode Agent (Manual Codebase Verification)
- **Status**: 🔴 PARTIAL — 37.5% terimplementasi (3/8 gap)
- **Terhubung ke**: DEV_DOCS-041a, DEV_DOCS-041b, DEV_DOCS-002, DEV_DOCS-010, ADR-002, ADR-003, ADR-009
- **Tipe Dokumen**: Dev Review / Handover / Memory Context

---

## EXECUTIVE SUMMARY

Dokumen ini memverifikasi apakah **8 gap dan rekomendasi** yang diidentifikasi di DEV_DOCS-041 (*Analisis API-Driven Architecture — Verifikasi Mendalam*) sudah diimplementasikan di codebase `sisfokol-laravel/`.

**Hasil:**

| Metrik | Nilai |
|--------|-------|
| Total gap direkomendasikan | 8 |
| ✅ Terimplementasi | 3 (37.5%) |
| ❌ Belum diimplementasi | 5 (62.5%) |
| Status keseluruhan | **PARTIAL** — infrastruktur API masih perlu pekerjaan signifikan |

---

## KONTeks: Apa itu DOC-041?

DOC-041 terdiri dari **2 dokumen** yang diterbitkan pada 2026-06-21:

1. **041_review_api_driven_readiness_20260621_2115.md** — Review awal kesiapan arsitektur API-Driven (Fase 1 vs Fase 2)
2. **041_analisis_api_driven_verifikasi_mendalam_20260621_2130.md** — Deep-dive verifikasi dengan 8 gap yang teridentifikasi + actionable next steps

**Kesimpulan utama DOC-041:**
> SISFOKOL v7 saat ini adalah **Domain-Modular Monolith dengan SSR (Blade)**, bukan pure API-Driven. Transisi ke API-Driven memerlukan penyelesaian gap infrastruktur terlebih dahulu.

---

## VERIFIKASI PER GAP

### Gap #1: Sanctum Config di `.env.example`

**Rekomendasi DOC-041:**
```
- [ ] Tambahkan SANCTUM_STATEFUL_DOMAINS ke .env.example
- [ ] Tambahkan SANCTUM_EXPIRATION (optional, default 1440)
```

**Status:** ❌ **BELUM DIIMPLEMENTASI**

**Bukti verifikasi:**
- File `sisfokol-laravel/.env.example` hanya berisi konfigurasi Laravel default (67 baris)
- **Tidak ditemukan** variabel `SANCTUM_*` manapun
- Tidak ada komentar referensi ke Sanctum/Fase 2

**File diperiksa:** `sisfokol-laravel/.env.example`

**Implikasi:** Setup Sanctum di Fase 2 akan memerlukan manual `.env` edits tanpa referensi dokumentasi di dalam repo.

---

### Gap #2: Folder `app/Http/Resources/`

**Rekomendasi DOC-041:**
```bash
mkdir sisfokol-laravel/app/Http/Resources
touch sisfokol-laravel/app/Http/Resources/.gitkeep
```

**Status:** ❌ **BELUM DIIMPLEMENTASI**

**Bukti verifikasi:**
- Folder `sisfokol-laravel/app/Http/Resources/` **tidak ada**
- Glob search `**/Http/Resources/**` hanya menemukan file di `vendor/laravel/framework/` (bawaan framework)
- Tidak ada file `*Resource.php` di direktori `app/`

**Implikasi:** Tidak ada template serialisasi API response. Developer Fase 2 harus membuat dari nol.

---

### Gap #3: Impersonation Token-Swap (Fase 2)

**Rekomendasi DOC-041:**
> Modul Impersonation menggunakan `lab404/laravel-impersonate` (session-based). Untuk API stateless, perlu custom Token Swap Endpoint.

**Status:** ⚠️ **N/A — SESUAI RENCANA (Fase 2)**

**Bukti verifikasi:**
- Paket `lab404/laravel-impersonate` terpasang dan berfungsi dengan session-based auth (Fase 1)
- Tidak ada token swap endpoint — ini sesuai karena ini memang ditargetkan Fase 2
- Middleware `BlockWhileImpersonating` sudah ada di `bootstrap/app.php`

**Catatan:** Gap ini **intentionally deferred** ke Fase 2. Tidak ada action item pending untuk Fase 1.

---

### Gap #4: Modular API Routes (`routes_api.php` per Module)

**Rekomendasi DOC-041:**
```php
// ModuleServiceProvider — tambahkan loading routes_api.php per module
if (file_exists($modulePath . '/routes_api.php')) {
    Route::middleware(['api', 'auth:sanctum'])
        ->prefix('api/v1')
        ->group($modulePath . '/routes_api.php');
}
```

**Status:** ❌ **BELUM DIIMPLEMENTASI**

**Bukti verifikasi:**
- **Tidak ada file** `routes_api.php` di manapun di codebase
- `ModuleServiceProvider::loadModuleRoutes()` hanya memuat `routes.php` per modul (web only)
- Semua modul core hanya punya 1 file route: `routes.php`
- Modul yang diperiksa: Auth, Academic, Evaluation, Finance, Presence

**Current state `routes/api.php`:**
```php
// Hanya 4 endpoint API:
POST /api/login
GET  /api/user          (auth:sanctum)
POST /api/logout        (auth:sanctum)
GET  /api/schedules/today (auth:sanctum)
```

**File diperiksa:**
- `sisfokol-laravel/app/Providers/ModuleServiceProvider.php` (line 53-65)
- `sisfokol-laravel/routes/api.php` (21 baris)
- Semua `app/Modules/*/routes.php`

**Implikasi:** Menambah API endpoint baru di Fase 2 akan semakin membuat `routes/api.php` menjadi monolitik. Modularisasi diperlukan sebelum penambahan endpoint masif.

---

### Gap #5: Audit Log Observer

**Rekomendasi DOC-041:**
> Observer untuk auto-audit perlu di-confirm atau di-implement. AuditLog model, observer, dan event listener belum jelas di codebase.

**Status:** ✅ **SUDAH DIIMPLEMENTASI**

**Bukti verifikasi:**

| Komponen | File | Status |
|----------|------|--------|
| AuditLog Model | `app/Modules/Auth/Models/AuditLog.php` | ✅ Ada |
| AuditLog Migration | `app/Modules/Auth/Database/Migrations/2026_06_20_000020_create_audit_logs_table.php` | ✅ Ada |
| AuditLogger Service | `app/Modules/Auth/Services/AuditLogger.php` | ✅ Ada (singleton) |
| UserObserver | `app/Modules/Auth/Observers/UserObserver.php` | ✅ Ada |
| SiswaObserver | `app/Modules/Academic/Observers/SiswaObserver.php` | ✅ Ada |
| AttendanceObserver | `app/Modules/Presence/Observers/AttendanceObserver.php` | ✅ Ada |
| Observer Registration | `app/Providers/AppServiceProvider.php` (line 48-50) | ✅ Terdaftar |
| AuditLogController | `app/Modules/Auth/Controllers/AuditLogController.php` | ✅ Ada |
| AuditLogPolicy | `app/Modules/Auth/Policies/AuditLogPolicy.php` | ✅ Ada |
| Unit Test | `tests/Unit/Auth/AuditLoggerTest.php` | ✅ Ada |

**Detail implementasi:**
- `AuditLog` menggunakan trait `MassPrunable` (auto-prune 2 tahun)
- Fillable: `tenant_id`, `user_id`, `event`, `model_type`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`
- `AuditLogger::log()` singleton menerima semua parameter audit
- Pattern: Observer → `AuditLogger::log()` → `AuditLog::create()` (synchronous, no event/listener)

**Catatan:** Implementasi menggunakan **Observer pattern langsung** (bukan Event/Listener pattern). Ini valid dan lebih sederhana. DOC-041 menyebut "EventListener belum jelas" — dalam praktiknya, Observer pattern yang dipilih adalah alternatif yang setara.

---

### Gap #6: BelongsToTenant Trait

**Rekomendasi DOC-041:**
> Trait `BelongsToTenant.php` belum ditemukan. Global Scope implementation tidak terlihat. Perlu verifikasi.

**Status:** ✅ **SUDAH DIIMPLEMENTASI**

**Bukti verifikasi:**

| Komponen | File | Status |
|----------|------|--------|
| BelongsToTenant Trait | `app/Models/Traits/BelongsToTenant.php` | ✅ Ada |
| TracksAuditColumns Trait | `app/Models/Traits/TracksAuditColumns.php` | ✅ Ada (bonus) |
| Unit Test | `tests/Unit/Models/Traits/BelongsToTenantTraitTest.php` | ✅ Ada |
| TenantContext Singleton | `app/Support/TenantContext.php` | ✅ Ada |
| ResolveTenant Middleware | `app/Http/Middleware/ResolveTenant.php` | ✅ Ada |

**Detail implementasi:**
- `BelongsToTenant` trait menggunakan `bootBelongsToTenant()` pattern (Laravel bootable trait)
- Menambahkan Global Scope `tenant` yang filter by `tenant_id` dari `TenantContext`
- Auto-fill `tenant_id` pada create
- Jika `TenantContext` uninitialized (superadmin), scope tidak apply — semua data terlihat
- `TenantContext` menyimpan `tenantId`, `branchId`, `settings` per-request
- `ResolveTenant` middleware membaca `tenant_id` dari authenticated user

**Penggunaan:** Semua domain models (Siswa, Guru, Kelas, Jadwal, Mapel, Finance, Presence, Evaluation, Kurikulum) sudah menggunakan trait ini. User model **tidak** menggunakan trait (by design — superadmin punya `tenant_id = null`).

---

### Gap #7: DTO Pattern / API Resources

**Rekomendasi DOC-041:**
> Data exposure tidak terkontrol. ApiScheduleController mengembalikan raw model collection tanpa field filtering.

**Status:** ❌ **BELUM DIIMPLEMENTASI**

**Bukti verifikasi:**

`sisfokol-laravel/app/Http/Controllers/Api/ScheduleController.php` — **masih mengembalikan raw collection:**
```php
public function today(Request $request)
{
    // ...
    $schedules = Schedule::with(['classroom', 'subject', 'room', 'timeSlot'])
        ->where('employee_id', $employee?->id)
        ->where('day_id', $dayNumber)
        ->orderBy('time_slot_id')
        ->get();

    return response()->json([
        'date' => Carbon::now()->format('Y-m-d'),
        'day' => Carbon::now()->locale('id')->dayName,
        'schedules' => $schedules,  // ← Raw model collection, tanpa Resource/DTO
    ]);
}
```

**Temuan tambahan:**
- Tidak ada class DTO manapun di codebase
- Tidak ada class `*Resource.php` di `app/`
- Semua API response menggunakan `response()->json()` dengan inline array / raw model

**Implikasi:** Risiko over-expose sensitive fields saat API endpoints ditambahkan di Fase 2.

---

### Gap #8: CORS Configuration

**Rekomendasi DOC-041:**
> CORS dan API Security Headers belum dikonfigurasi. Akan menjadi masalah saat frontend SPA dipisahkan.

**Status:** ❌ **BELUM DIIMPLEMENTASI**

**Bukti verifikasi:**
- `sisfokol-laravel/config/cors.php` — **TIDAK ADA** (tidak dipublish dari framework)
- `sisfokol-laravel/bootstrap/app.php` — **tidak ada** registrasi middleware CORS eksplisit
- Tidak ada referensi "cors" di `config/`, `app/`, `routes/`, atau `composer.json`
- Laravel 11 `HandleCors` middleware aktif secara implisit, menggunakan default framework (allow all origins)
- Default framework CORS: `allowed_origins: ['*']`, `supports_credentials: false`

**Implikasi:** Saat frontend SPA dipisahkan (Fase 2), CORS perlu dikonfigurasi dengan proper allowed origins. Saat ini menggunakan "allow all" yang tidak aman untuk production.

---

## RINGKASAN MATRIK VERIFIKASI

| # | Gap | Rekomendasi DOC-041 | Status Codebase | Tindakan |
|---|-----|---------------------|-----------------|----------|
| 1 | Sanctum config `.env.example` | Tambahkan `SANCTUM_*` variables | ❌ Belum | Perlu ditambahkan |
| 2 | Folder `app/Http/Resources/` | Buat folder + .gitkeep | ❌ Belum | Perlu dibuat |
| 3 | Impersonation Token-Swap | Custom endpoint Fase 2 | ⚠️ N/A | Deferred ke Fase 2 |
| 4 | Modular API Routes | `routes_api.php` per module | ❌ Belum | Perlu implementasi |
| 5 | Audit Log Observer | Confirm/create observers | ✅ Sudah | **DONE** |
| 6 | BelongsToTenant Trait | Verify Global Scope | ✅ Sudah | **DONE** |
| 7 | DTO / API Resources | Gunakan Resource/DTO | ❌ Belum | Perlu implementasi |
| 8 | CORS Configuration | Publish `config/cors.php` | ❌ Belum | Perlu dikonfigurasi |

---

## ACTION ITEMS TERKAIT

### Immediate (Fase 1 Akhir) — dari DOC-041

| # | Action Item | Prioritas | Estimasi |
|---|-------------|-----------|----------|
| A1 | Tambahkan `SANCTUM_STATEFUL_DOMAINS=sisfokol-laravel.test` ke `.env.example` | Medium | 2 min |
| A2 | Buat folder `app/Http/Resources/` dengan `.gitkeep` | Medium | 2 min |
| A3 | Buat `ScheduleResource.php` sebagai contoh/template | Medium | 15 min |
| A4 | Update `ScheduleController` untuk menggunakan `ScheduleResource` | Medium | 15 min |

### Fase 2 Kickoff

| # | Action Item | Prioritas | Estimasi |
|---|-------------|-----------|----------|
| B1 | Publish `config/cors.php` dan konfigurasi allowed origins | High | 30 min |
| B2 | Implementasi loading `routes_api.php` di `ModuleServiceProvider` | High | 1 jam |
| B3 | Buat template API routes per modul core | High | 2 jam |
| B4 | Implementasi DTO pattern untuk API responses | Medium | 3 jam |
| B5 | Implementasi Token Swap Impersonation endpoint | Medium | 2 jam |
| B6 | Setup API versioning strategy (v1, v2) | Low | 1 jam |

### Long-Term (Post-MVP)

| # | Action Item | Prioritas |
|---|-------------|-----------|
| C1 | Setup OpenAPI/Swagger specification | Low |
| C2 | Document API security guidelines | Low |
| C3 | Pertimbangan Inertia.js adoption (DOC-041 Langkah 1) | Low |

---

## CATATAN UNTUK AGENTIC AI (HANDOVER CONTEXT)

### State Arsitektur Saat Ini
- **Fase saat ini**: Fase 1 — MVP Blade-SSR Monolith
- **Routing**: 156+ web routes (Blade) vs 4 API routes
- **Auth**: Dual guard (web=session, sanctum=token). Sanctum aktif tapi minimal
- **Frontend stack**: Tailwind CSS + Vite (tanpa SPA framework)
- **Multi-tenancy**: ✅ Fully implemented via `BelongsToTenant` trait + `TenantContext`
- **Audit system**: ✅ Fully implemented via Observer pattern + `AuditLogger` service

### Jangan Asumsikan
1. ❌ Jangan asumsikan `app/Http/Resources/` ada — **tidak ada**
2. ❌ Jangan asumsikan Sanctum config ada di `.env.example` — **tidak ada**
3. ❌ Jangan asumsikan CORS sudah dikonfigurasi — **menggunakan default allow-all**
4. ❌ Jangan asumsikan modul punya API route terpisah — **semua inline di `routes/api.php`**
5. ✅ AMAN asumsikan BelongsToTenant sudah bekerja — **fully implemented**
6. ✅ AMAN asumsikan audit observer sudah aktif — **3 observers terdaftar**

### File-File Kunci Yang Perlu Diperiksa Saat Kerja Fase 2
```
sisfokol-laravel/routes/api.php                          — semua API routes saat ini
sisfokol-laravel/app/Http/Controllers/Api/                — API controllers
sisfokol-laravel/app/Providers/ModuleServiceProvider.php  — modul route loading
sisfokol-laravel/bootstrap/app.php                        — middleware registration
sisfokol-laravel/.env.example                             — environment config
sisfokol-laravel/app/Models/Traits/BelongsToTenant.php    — tenant isolation
sisfokol-laravel/app/Modules/Auth/Services/AuditLogger.php — audit service
sisfokol-laravel/app/Support/TenantContext.php            — tenant context
```

---

## REFERENSI DOKUMEN TERKAIT

| Dokumen | Isi |
|---------|-----|
| **DEV_DOCS-041a** | `041_review_api_driven_readiness_20260621_2115.md` — Review awal kesiapan API |
| **DEV_DOCS-041b** | `041_analisis_api_driven_verifikasi_mendalam_20260621_2130.md` — Deep-dive 8 gap |
| **DEV_DOCS-002** | `002_bagian2_tenancy_auth_rbac_impersonation_20260620_0713.md` — Blueprint auth layer |
| **DEV_DOCS-010** | `010_bagian6_folder_structure_techstack_deployment_20260620_0830.md` — Infrastructure plan |
| **ADR-002** | Multi-Tenancy (tenant isolation) |
| **ADR-003** | Authentication Guards (web + sanctum) |
| **ADR-009** | Plugin Architecture & UI Dynamism |

---

**Document Status**: ✅ VERIFIED — Review selesai
**Last Updated**: 2026-06-22
**Next Review**: Setelah action items Immediate (A1-A4) dikerjakan, atau saat Fase 2 kickoff
