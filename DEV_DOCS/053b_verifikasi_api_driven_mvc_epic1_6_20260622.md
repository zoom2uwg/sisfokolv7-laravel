# DEV_DOCS-053b: Verifikasi API-Driven MVC — Epic 1-6 vs Realita di Disk

- **Tanggal:** 2026-06-22
- **Status:** ✅ VERIFIKASI SELESAI
- **Penulis:** ZCode
- **Metode:** Verifikasi fisik langsung pada `sisfokol-laravel/` — no hallusinasi, no overclaim
- **Prinsip:** Cek file ada/tidak, cek isinya, cek apakah terhubung ke runtime

---

## ⚡ EXECUTIVE SUMMARY

**Realita:** Aplikasi ini adalah **pure Blade-SSR monolith**. API-Driven MVC **TIDAK BERFUNGSI** sama sekali.

| Aspek | Klaim Dokumen | Realita di Disk |
|-------|--------------|-----------------|
| API Routes aktif | "4 rute API aktif" | ❌ `routes/api.php` **TIDAK DI-LOAD** oleh `bootstrap/app.php` |
| Sanctum terpasang | "Sanctum aktif" | ❌ **TIDAK ADA** di `composer.json` / `composer.lock` |
| API Resources | "placeholder siap" | ❌ Folder `app/Http/Resources/` **TIDAK ADA** |
| CORS config | "dikonfigurasi" | ❌ `config/cors.php` **TIDAK ADA** |
| Modular API routes | "siap Fase 2" | ❌ **0 file** `routes_api.php` di seluruh codebase |
| Score kesiapan API | "6.5/10" (DOC-041b) | 🔴 **~1.5/10** (hasil audit DOC-051) |

---

## 1. INFRASTRUKTUR API GLOBAL — STATUS FISIK

### 1.1 bootstrap/app.php — ROUTING

```php
// File: bootstrap/app.php (line 8-12)
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',        // ✅ ADA
        commands: __DIR__.'/../routes/console.php', // ✅ ADA
        health: '/up',                              // ✅ ADA
        // ❌ TIDAK ADA 'api:' key → routes/api.php TIDAK PERNAH DI-LOAD
    )
```

**Verdict:** 🔴 **routes/api.php TIDAK TERDAFTAR di aplikasi.** Semua rute API yang diklaim "aktif" **tidak bisa diakses**.

### 1.2 routes/api.php — ISI FILE

```php
// File: routes/api.php (21 baris) — ADA di disk tapi TIDAK DI-LOAD
Route::post('/login', [ApiAuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [ApiAuthController::class, 'user']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/schedules/today', [ApiScheduleController::class, 'today']);
});
```

**Verdict:** ⚠️ File ada, kode ada, tapi **dead code** — tidak pernah dimuat aplikasi.

### 1.3 Laravel Sanctum — DEPENDENCY

| Komponen | Status |
|----------|--------|
| `laravel/sanctum` di `composer.json` | ❌ **TIDAK ADA** |
| `laravel/sanctum` di `composer.lock` | ❌ **TIDAK ADA** |
| `config/sanctum.php` | ❌ **TIDAK ADA** |
| `HasApiTokens` trait di `User.php` | ❌ **TIDAK ADA** |
| Migrasi `personal_access_tokens` | ❌ **TIDAK ADA** |
| Guard `sanctum` di `config/auth.php` | ❌ **TIDAK ADA** (hanya guard `web`) |

**Verdict:** 🔴 **Sanctum TIDAK TERPASANG.** `createToken()` di `Api/AuthController.php:30` akan **CRASH** saat dipanggil.

### 1.4 CORS & Resources

| Komponen | Status |
|----------|--------|
| `config/cors.php` | ❌ **TIDAK ADA** |
| `app/Http/Resources/` directory | ❌ **TIDAK ADA** |
| `app/Http/Resources/*.php` | ❌ **0 file** |
| `routes_api.php` per modul | ❌ **0 file** di seluruh codebase |

---

## 2. VERIFIKASI PER EPIC — API CONTROLLER & ROUTE

### EPIC 1: Setup & Fondasi

| Komponen API | File di Disk | Status |
|--------------|-------------|--------|
| API Auth Controller | `app/Http/Controllers/Api/AuthController.php` | ⚠️ Ada, tapi **akan crash** (butuh Sanctum) |
| API Schedule Controller | `app/Http/Controllers/Api/ScheduleController.php` | ⚠️ Ada, expose raw model |
| API routes | `routes/api.php` | ⚠️ Ada, tapi **tidak di-load** |
| `config/auth.php` guard sanctum | — | ❌ **TIDAK ADA** |
| `User.php` `HasApiTokens` | — | ❌ **TIDAK ADA** |
| `config/sanctum.php` | — | ❌ **TIDAK ADA** |
| `config/cors.php` | — | ❌ **TIDAK ADA** |
| `app/Http/Resources/` | — | ❌ **TIDAK ADA** |
| API test | `tests/Feature/Api/AuthApiTest.php` | ❌ **TIDAK ADA** |

**Score API Epic 1:** 🔴 **~10%** — Hanya skeleton controller yang tidak terhubung runtime.

---

### EPIC 2: Auth Module

| Komponen API | File di Disk | Status |
|--------------|-------------|--------|
| Web Auth Controller | `app/Modules/Auth/Controllers/AuthController.php` | ✅ Ada, return `view()` (Blade SSR) |
| API Auth Controller | `app/Http/Controllers/Api/AuthController.php` | ⚠️ Ada, return `response()->json()` — tapi rute tidak aktif |
| Login API route | `routes/api.php:13` | ❌ **TIDAK AKTIF** (tidak di-load) |
| User API route | `routes/api.php:16` | ❌ **TIDAK AKTIF** |
| Logout API route | `routes/api.php:17` | ❌ **TIDAK AKTIF** |
| API throttle | — | ❌ **TIDAK ADA** (hanya web `throttle:5,1`) |
| API test | — | ❌ **TIDAK ADA** |

**Score API Epic 2:** 🔴 **~5%** — Controller ada tapi tidak bisa diakses.

**Detail:** `Api/AuthController::login()` (line 30) memanggil `$user->createToken($request->device_name)->plainTextToken` — ini akan **fatal error** karena:
1. `User` model tidak punya `HasApiTokens` trait
2. Tabel `personal_access_tokens` tidak ada
3. Sanctum package tidak terpasang

---

### EPIC 3: RBAC Builder

| Komponen API | File di Disk | Status |
|--------------|-------------|--------|
| Web RBAC Controllers | 4 controllers (`RbacRoleController`, dll) | ✅ Ada, return `view()` (Blade SSR) |
| `RbacRoleController` JSON response | Line 28: `return response()->json(['status' => 'ok'])` | ✅ Ada — **satu-satunya controller module yang return JSON** |
| API RBAC routes | — | ❌ **TIDAK ADA** |
| API RBAC Resources | — | ❌ **TIDAK ADA** |
| API Permission Matrix endpoint | — | ❌ **TIDAK ADA** |

**Score API Epic 3:** 🔴 **~5%** — Hanya 1 JSON response di antara 4 controller Blade SSR.

---

### EPIC 4: Plugin Infrastructure

| Komponen API | File di Disk | Status |
|--------------|-------------|--------|
| Web Plugin Controller | `app/Modules/Auth/Controllers/PluginController.php` | ✅ Ada, return `view()` (Blade SSR) |
| API Plugin routes | — | ❌ **TIDAK ADA** |
| API Plugin activate/deactivate endpoint | — | ❌ **TIDAK ADA** |
| API Plugin status endpoint | — | ❌ **TIDAK ADA** |

**Score API Epic 4:** 🔴 **~0%** — Pure Blade SSR, tidak ada API layer.

---

### EPIC 5: Academic Module

| Komponen API | File di Disk | Status |
|--------------|-------------|--------|
| Web SiswaController | `app/Modules/Academic/Controllers/SiswaController.php` | ✅ Ada, return `view()` (Blade SSR) |
| API Siswa CRUD | — | ❌ **TIDAK ADA** |
| API routes per modul Academic | — | ❌ **TIDAK ADA** (`routes_api.php` tidak ada) |
| `SiswaResource` | — | ❌ **TIDAK ADA** |
| `KelasResource` | — | ❌ **TIDAK ADA** |
| `MapelResource` | — | ❌ **TIDAK ADA** |
| `JadwalResource` | — | ❌ **TIDAK ADA** |
| API test Academic | — | ❌ **TIDAK ADA** |

**Score API Epic 5:** 🔴 **~0%** — Pure Blade SSR, tidak ada API layer.

**Detail:** `SiswaController` hanya punya method `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()` yang semuanya return `view()` atau `redirect()`.

---

### EPIC 6: Evaluation Module

| Komponen API | File di Disk | Status |
|--------------|-------------|--------|
| Web GradeEntryController | `app/Modules/Evaluation/Controllers/GradeEntryController.php` | ✅ Ada, **campuran** — `index()` return view, `storeScores()` return JSON |
| Web RaporController | `app/Modules/Evaluation/Controllers/RaporController.php` | ✅ Ada, return `view()` + PDF |
| Web CurriculumController | `app/Modules/Evaluation/Controllers/CurriculumController.php` | ✅ Ada, return `view()` |
| API Grade routes | — | ❌ **TIDAK ADA** |
| API Rapor routes | — | ❌ **TIDAK ADA** |
| API Curriculum routes | — | ❌ **TIDAK ADA** |
| `GradeResource` | — | ❌ **TIDAK ADA** |
| `RaporResource` | — | ❌ **TIDAK ADA** |
| API test Evaluation | — | ❌ **TIDAK ADA** |

**Score API Epic 6:** 🔴 **~10%** — `GradeEntryController.storeScores()` return JSON (AJAX), tapi ini untuk web AJAX call, bukan API endpoint terproteksi Sanctum.

**Detail JSON responses di GradeEntryController:**
```php
// Line 182-185: storeAssessment() — return JSON
return response()->json(['status' => 'success', 'assessment' => $assessment]);

// Line 232-235: storeScores() — return JSON
return response()->json(['status' => 'success', 'message' => 'Nilai berhasil disimpan...']);
```
Ini dipanggil via AJAX dari Blade view, bukan sebagai API endpoint terpisah.

---

## 3. YANG ADA vs YANG TIDAK ADA — INVENTORY LENGKAP

### 3.1 Yang ADA (skeleton tanpa otak)

| # | Komponen | Path | Fungsional? |
|---|----------|------|-------------|
| 1 | `routes/api.php` | `routes/api.php` | ❌ Tidak di-load |
| 2 | `Api\AuthController` | `app/Http/Controllers/Api/AuthController.php` | ❌ Akan crash (butuh Sanctum) |
| 3 | `Api\ScheduleController` | `app/Http/Controllers/Api/ScheduleController.php` | ❌ Rute tidak aktif, expose raw model |
| 4 | `config/auth.php` | `config/auth.php` | ✅ Ada, tapi hanya guard `web` |
| 5 | `.env.example` | `.env.example` | ✅ Ada, tanpa `SANCTUM_*` |

### 3.2 Yang TIDAK ADA (missing total)

| # | Komponen | Ekspektasi | Status |
|---|----------|-----------|--------|
| 1 | `laravel/sanctum` | Dependency inti token API | ❌ Tidak di composer.json/lock |
| 2 | `config/sanctum.php` | Konfigurasi Sanctum | ❌ Tidak ada |
| 3 | `config/cors.php` | Konfigurasi CORS | ❌ Tidak ada |
| 4 | `app/Http/Resources/` | API Resource / DTO | ❌ Folder tidak ada |
| 5 | Migrasi `personal_access_tokens` | Tabel token Sanctum | ❌ Tidak ada |
| 6 | `HasApiTokens` di `User` | Method `createToken()` | ❌ Tidak dipakai |
| 7 | `routes_api.php` per modul | Modular API routing | ❌ 0 file |
| 8 | `api:` key di `bootstrap/app.php` | Loader rute API | ❌ Tidak ada |
| 9 | API throttle/rate limiter | Proteksi brute force | ❌ Tidak ada |
| 10 | `BaseApiController` | Helper JSON response | ❌ Tidak ada |
| 11 | JSON ExceptionHandler | Error response format | ❌ Tidak ada |
| 12 | API test files | Feature test API | ❌ 0 file |

### 3.3 Yang Bisa Dipakai untuk API (fondasi existing)

| # | Komponen | Path | Relevansi API |
|---|----------|------|---------------|
| 1 | `BelongsToTenant` trait | `app/Models/Traits/BelongsToTenant.php` | ✅ Auto-isolasi tenant saat API query |
| 2 | `TenantContext` singleton | `app/Support/TenantContext.php` | ✅ Bisa resolve dari token |
| 3 | `ResolveTenant` middleware | `app/Http/Middleware/ResolveTenant.php` | ✅ Bisa dipakai di API stack |
| 4 | `AuditLogger` + Observers | `app/Modules/Auth/Services/AuditLogger.php` | ✅ Audit API mutations |
| 5 | RBAC Spatie | `spatie/laravel-permission` | ✅ Permission middleware bisa di API |
| 6 | `BlockWhileImpersonating` | `app/Http/Middleware/BlockWhileImpersonating.php` | ✅ Untuk impersonation API |
| 7 | JSON response di GradeEntryController | `app/Modules/Evaluation/Controllers/GradeEntryController.php` | ⚠️ Pattern bisa di-reuse |

---

## 4. SKOR KESIAPAN API PER EPIC

| Epic | Klaim Dokumen | Realita di Disk | Gap |
|------|--------------|-----------------|-----|
| 1 — Setup & Fondasi | "API skeleton siap" | **~10%** | Sanctum tidak terpasang, routes tidak di-load |
| 2 — Auth Module | "Auth API minimal aktif" | **~5%** | Controller ada tapi crash, rute tidak aktif |
| 3 — RBAC Builder | "API-ready architecture" | **~5%** | 1 JSON response, 0 API routes |
| 4 — Plugin Infrastructure | "Plugin API siap" | **~0%** | Pure Blade SSR |
| 5 — Academic Module | "Modular, bisa di-API-kan" | **~0%** | Pure Blade SSR, 0 API routes |
| 6 — Evaluation Module | "AJAX grading aktif" | **~10%** | AJAX JSON (web), bukan API endpoint |
| **TOTAL** | **"6.5/10 API-Ready"** | **~1.5/10** | **Tidak ada API layer yang berfungsi** |

---

## 5. PERBANDINGAN: KONTEN JSON vs BLADE SSR

### Controller yang return `response()->json()` (API-like)

| Controller | Method | Line | Konteks |
|-----------|--------|------|---------|
| `Api/AuthController` | `login()` | 32 | ❌ Rute tidak aktif |
| `Api/AuthController` | `user()` | 47 | ❌ Rute tidak aktif |
| `Api/AuthController` | `logout()` | 59 | ❌ Rute tidak aktif |
| `Api/ScheduleController` | `today()` | 25 | ❌ Rute tidak aktif |
| `RbacRoleController` | (unknown) | 28 | ⚠️ AJAX call dari Blade |
| `GradeEntryController` | `storeAssessment()` | 182 | ⚠️ AJAX call dari Blade |
| `GradeEntryController` | `storeScores()` | 232 | ⚠️ AJAX call dari Blade |
| `PresensiController` | `storeScan()` | 50 | ⚠️ AJAX call dari Blade |

**Total:** 8 method return JSON — tapi **0 yang merupakan API endpoint terproteksi Sanctum**.

### Controller yang return `view()` (Blade SSR)

| Module | Controllers | Semua return view() |
|--------|-----------|-------------------|
| Auth (Module) | 7 controllers | ✅ Ya |
| Academic | 1 controller (SiswaController) | ✅ Ya |
| Evaluation | 3 controllers | ✅ Ya (kecuali AJAX di GradeEntry) |
| Finance | 5 controllers | ✅ Ya |
| Presence | 4 controllers | ✅ Ya (kecuali AJAX di Presensi) |
| Admin (Core) | 9 controllers | ✅ Ya |
| Teacher (Core) | 5 controllers | ✅ Ya |
| Other roles | 8 controllers | ✅ Ya |

**Total:** ~42 controller return Blade view vs 0 API endpoint.

---

## 6. KESIMPULAN

### Fakta dari Disk:

1. **API-Driven MVC TIDAK ADA** — aplikasi ini 100% Blade-SSR monolith
2. **routes/api.php** ada di disk tapi **tidak pernah di-load** oleh `bootstrap/app.php`
3. **Sanctum** tidak terpasang — tidak di `composer.json`, tidak ada config, tidak ada trait
4. **0 API Resource** — folder `app/Http/Resources/` tidak ada
5. **0 routes_api.php** per modul — tidak ada modular API routing
6. **0 API test** — tidak ada test file untuk API
7. **8 method return JSON** — tapi semuanya untuk AJAX web call, bukan API endpoint
8. **42+ controller return Blade view** — pure SSR

### Yang Perlu Dilakukan untuk API-Driven:

| Fase | Estimasi | Deliverable |
|------|----------|-------------|
| **A — Fondasi** | 1 sesi | Install Sanctum, fix bootstrap, buat CORS + 2 Resource, 4 rute API aktif |
| **B — Modular** | 2-3 sesi | 6 `routes_api.php`, 10+ Resource, JSON ExceptionHandler |
| **C — Hardening** | 2 sesi | Token-swap impersonation, CORS restrict, endpoint catalog |
| **Total** | **5-6 sesi** | API-Driven MVC yang benar-benar berfungsi |

---

## 📎 REFERENSI

- **DEV_DOCS-041** — Review kesiapan API (skor 6.5/10 — **overclaim**)
- **DEV_DOCS-044** — Verifikasi 8 gap API (valid, tapi luput 3 temuan baru)
- **DEV_DOCS-051** — Audit overclaim API + rencana 3 fase (**sumber kebenaran API**)
- **DEV_DOCS-053** — Master implementation plan (TAHAP 4 = API Infrastructure)
- **DEV_DOCS-053a** — Verifikasi fisik codebase Epic 1-9

---

*Dokumen ini dibuat oleh ZCode berdasarkan verifikasi fisik langsung terhadap file di disk.*
*Tidak ada hallusinasi — semua claim dibuktikan dengan file path, line number, dan isi file.*
