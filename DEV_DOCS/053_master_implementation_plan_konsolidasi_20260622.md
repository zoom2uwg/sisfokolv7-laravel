# DEV_DOCS-053: Master Implementation Plan — Konsolidasi & Eksekusi Gap Epic 1-12

- **Tanggal:** 2026-06-22
- **Status:** 📋 SIAP DIEKSEKUSI
- **Penulis:** ZCode (berdasarkan audit DEV_DOCS 030-052)
- **Sumber kebenaran:** DEV_DOCS-012 (master implementation plan)
- **Tujuan:** Menyatukan seluruh temuan gap dari dokumen 030-050 ke dalam satu rencana eksekusi terstruktur.

---

## ⚡ EXECUTIVE SUMMARY

Berdasarkan audit menyeluruh terhadap **22 dokumen DEV_DOCS (030-052)** dan **verifikasi fisik codebase**, ditemukan:

- **12 Epic** telah direncanakan, hanya **3 yang benar-benar selesai** (Epic 1-3)
- **5 Epic** mengklaim selesai tapi memiliki gap kritis (Epic 4-9)
- **3 Epic** belum dimulai (Epic 10-12)
- **"Ilusi Penyelesaian"** — 112 tests green tapi sistem tidak fungsional di dunia nyata

**Root Cause:** Dualisme model (`siswa` vs `students`) yang menyebabkan seluruh modul tidak saling terhubung.

---

## 📊 STATUS REAL SETIAP EPIC (Hasil Verifikasi Fisik)

| Epic | Nama | Klaim Dokumen | Status Real | Gap Utama |
|------|------|---------------|-------------|-----------|
| 1 | Setup & Fondasi | ✅ 100% | **~90%** | Model core belum pakai `BelongsToTenant` |
| 2 | Auth Module | ✅ 100% | **~95%** | `cors.php` belum dipublish, Sanctum belum ada |
| 3 | RBAC Builder | ✅ 100% | **~95%** | API guard belum ada |
| 4 | Plugin Infrastructure | ✅ 100% | **~80%** | Event hook core belum dispatch |
| 5 | Academic Module | ✅ 100% | **~85%** | Parallel universe (`siswa` vs `students`) |
| 6 | Evaluation Module | ✅ ~85% | **~50%** | 3 fatal gap (controller, event, type mismatch) |
| 7 | Finance Module | ✅ ~90% | **~60%** | Dual kasir, duplikasi view |
| 8 | Presence Module | ✅ ~100% | **~70%** | Roster presensi terbelah |
| 9 | Plugin Kurikulum | ✅ 100% | **~30%** | Dead code — subscriber tidak terpanggil |
| 10 | 8 Plugin Scaffold | ⏳ Pending | **~5%** | Folder kosong, scaffold only |
| 11 | ETL Pipeline | ⏳ Pending | **~0%** | Draft skeleton saja |
| 12 | Testing & Deployment | ⏳ Pending | **~0%** | Belum dimulai |

---

## 🗺️ RENCANA EKSEKUSI BERTAHAP

### TAHAP 1: Unifikasi Model & Database [KRITIS]
**Estimasi:** 1 sesi | **Dokumen sumber:** DEV_DOCS-045, DEV_DOCS-049

**Tujuan:** Menghancurkan "Parallel Universes" — akar masalah semua modul.

#### Task 1.1: Mapping Model Core ke Tabel Modular
```
[ ] app/Models/Student.php       → protected $table = 'siswa';
[ ] app/Models/Classroom.php     → protected $table = 'kelas';
[ ] app/Models/Subject.php       → protected $table = 'mapel';
[ ] app/Models/AcademicYear.php  → protected $table = 'tahun_ajaran';
```

#### Task 1.2: Tambah Trait Tenancy ke Model Core
```
[ ] Student.php       → use BelongsToTenant, TracksAuditColumns;
[ ] Classroom.php     → use BelongsToTenant, TracksAuditColumns;
[ ] Subject.php       → use BelongsToTenant, TracksAuditColumns;
[ ] AcademicYear.php  → use BelongsToTenant, TracksAuditColumns;
```

#### Task 1.3: Normalisasi Kolom (Accessor/Mutator)
```
[ ] Student: name→nama, nisn→nisn, gender→jenis_kelamin, dst
[ ] Classroom: name→nama, grade→tingkat
[ ] Subject: name→nama, code→kode
[ ] AcademicYear: name→nama, start_date→tanggal_mulai, end_date→tanggal_selesai
```

#### Task 1.4: Update Foreign Key di Seluruh Migration
```
[ ] Grep & update: foreignId('student_id')->constrained('students') → constrained('siswa')
[ ] Grep & update: foreignId('classroom_id')->constrained('classrooms') → constrained('kelas')
[ ] Grep & update: foreignId('subject_id')->constrained('subjects') → constrained('mapel')
[ ] Grep & update: foreignId('academic_year_id')->constrained('academic_years') → constrained('tahun_ajaran')
```

#### Task 1.5: Drop Tabel Redundan
```
[ ] php83 artisan migrate:rollback (selektif, backup dulu)
[ ] DROP TABLE students, classrooms, subjects, academic_years
[ ] php83 artisan migrate
```

#### Task 1.6: Update Seeder & Test
``[ ] Update RolePermissionSeeder — sesuaikan referensi tabel[ ] Update FieldSeeder — sesuaikan FQCN model[ ] php83 artisan db:seed --class=RolePermissionSeeder[ ] php83 artisan test``

**DoD Tahap 1:**
- ✅ Input siswa di Academic → langsung muncul di Evaluation & Finance
- ✅ Tidak ada tabel ganda lagi di database
- ✅ Semua test green

---

### TAHAP 2: Aktivasi Event Hook & Fix Crash [KRITIS]
**Estimasi:** 1 sesi | **Dokumen sumber:** DEV_DOCS-045, DEV_DOCS-049

**Tujuan:** Menghidupkan "kode mati" dan menghilangkan crash runtime.

#### Task 2.1: Verifikasi CurriculumController
```
[ ] Cek app/Modules/Evaluation/Controllers/CurriculumController.php ada
[ ] Cek rute /evaluation/curriculum tidak crash
[ ] Cek view evaluation/curriculum/index.blade.php ada
```

#### Task 2.2: Inject Event Dispatcher ke GradeEntryController
```php
// app/Modules/Evaluation/Controllers/GradeEntryController.php
// Sebelum render form nilai:
$event = new EvaluationResolveFramework($subject, $classroom);
event($event);
$framework = $event->framework;
```
```
[ ] Tambahkan event dispatch di method create()/edit()
[ ] Passing $framework ke view
```

#### Task 2.3: Inject Event Dispatcher ke RaporGeneratorService
```php
// app/Modules/Evaluation/Services/RaporGeneratorService.php
// Sebelum render PDF:
$event = new RaportRenderSection($siswa, $academicYear, $semester);
event($event);
$customSections = $event->sections;
```
```
[ ] Tambahkan event dispatch di method generate()
[ ] Merge $customSections ke output PDF
```

#### Task 2.4: Fix Type Hint Parameter Event
```
[ ] EvaluationResolveFramework — ubah parameter ke model unified (Student, Classroom)
[ ] RaportRenderSection — ubah parameter ke model unified
[ ] Update subscriber di Plugin/Kurikulum/Subscribers/ sesuaikan
```

#### Task 2.5: Verifikasi Plugin Kurikulum Aktif
```
[ ] Cek KurikulumServiceProvider terdaftar
[ ] Cek EvaluationFrameworkSubscriber menerima event
[ ] Cek RaporSectionSubscriber menerima event
[ ] Test:ubah data CP/TP → tercermin di form nilai
```

**DoD Tahap 2:**
- ✅ Akses /evaluation/curriculum tanpa crash
- ✅ Form nilai menampilkan framework kurikulum dari plugin
- ✅ PDF rapor menampilkan section dari plugin

---

### TAHAP 3: Konsolidasi Finance & Presence [TINGGI]
**Estimasi:** 1 sesi | **Dokumen sumber:** DEV_DOCS-045

**Tujuan:** Menghilangkan dualisme kasir dan presensi.

#### Task 3.1: Hapus Duplikasi Kasir
```
[ ] Identifikasi StudentPaymentController (Core) — hapus atau redirect
[ ] Tetapkan PembayaranController (Modular) sebagai kasir tunggal
[ ] Hapus view duplikat:
    - resources/views/finance/payment-items/ (hapus, gunakan item-pembayaran/)
    - resources/views/finance/student-bills/ (hapus, gunakan tagihan/)
    - resources/views/finance/student-payments/ (hapus, gunakan pembayaran/)
    - resources/views/finance/student-savings/ (hapus, gunakan tabungan/)
[ ] Update route references
```

#### Task 3.2: Unifikasi Presensi
```
[ ] QrScannerService → gunakan model Student (tabel siswa) yang sudah unified
[ ] AttendanceController → gunakan model Student yang sama
[ ] Hapus referensi attendable_type ganda
[ ] Test: scan QR di gerbang → muncul di rekap guru
```

#### Task 3.3: Audit Foreign Key
```
[ ] php83 artisan migrate:status — pastikan semua FK konsisten
[ ] Grep untuk constrained('students') — harus 0 hasil
[ ] Grep untuk constrained('classrooms') — harus 0 hasil
```

**DoD Tahap 3:**
- ✅ Hanya 1 kasir yang berfungsi
- ✅ Presensi gerbang terhubung ke rekap guru
- ✅ Tidak ada view duplikat

---

### TAHAP 4: Install API Infrastructure [SEDANG]
**Estimasi:** 1-2 sesi | **Dokumen sumber:** DEV_DOCS-051

**Tujuan:** Mengubah aplikasi dari pure Blade-SSR ke API-Ready.

#### Task 4.1: Install & Konfigurasi Sanctum
```
[ ] php83 composer require laravel/sanctum
[ ] php83 artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
[ ] Tambah HasApiTokens trait ke User model
[ ] php83 artisan migrate (tabel personal_access_tokens)
```

#### Task 4.2: Aktifkan API Routing
```php
// bootstrap/app.php — tambah api routing:
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',  // ← TAMBAH INI
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```
```
[ ] Update bootstrap/app.php
[ ] Buat routes/api.php jika belum ada
[ ] Test: php83 artisan route:list --path=api
```

#### Task 4.3: Buat API Resources & CORS
```
[ ] Buat app/Http/Resources/ directory
[ ] Buat StudentResource, ClassroomResource, SubjectResource
[ ] Publish config/cors.php: php83 artisan vendor:publish --tag=cors-config
[ ] Update config/cors.php — sesuaikan allowed_origins
[ ] Update .env: SANCTUM_STATEFUL_DOMAINS
```

#### Task 4.4: Buat API Controller Dasar
```
[ ] app/Http/Controllers/Api/AuthController.php — login/logout/register
[ ] app/Http/Controllers/Api/StudentController.php — CRUD siswa
[ ] Test: curl login → dapat token → akses protected route
```

**DoD Tahap 4:**
- ✅ `php83 artisan route:list --path=api` menunjukkan rute API
- ✅ Login API → dapat token → akses data
- ✅ CORS terkonfigurasi

---

### TAHAP 4.5: UI Component Library [SEDIANG]
**Estimasi:** 1-2 sesi | **Dokumen sumber:** ADR-011, DEV_DOCS-053c

**Tujuan:** Membangun reusable Blade components yang konsisten, modern, responsive.

#### Task 4.5.1: Buat UI Primitives
```
[ ] components/ui/card.blade.php        — Glassmorphism card
[ ] components/ui/stat-card.blade.php   — Dashboard stat card
[ ] components/ui/badge.blade.php       — Status badge
[ ] components/ui/button.blade.php      — Button variants
[ ] components/ui/modal.blade.php       — Modal dialog
[ ] components/ui/alert.blade.php       — Toast alert (update existing)
[ ] components/ui/empty-state.blade.php — Empty state illustration
```

#### Task 4.5.2: Buat Form Components
```
[ ] components/form/group.blade.php     — Label + input + error
[ ] components/form/input.blade.php     — Text input
[ ] components/form/select.blade.php    — Select dropdown
[ ] components/form/textarea.blade.php  — Textarea
[ ] components/form/checkbox.blade.php  — Checkbox
```

#### Task 4.5.3: Buat Table Components
```
[ ] components/table/wrapper.blade.php  — Responsive table
[ ] components/table/th.blade.php       — Table header
[ ] components/table/td.blade.php       — Table cell
```

#### Task 4.5.4: Buat Shared Partials
```
[ ] partials/search-form.blade.php      — Reusable search
[ ] partials/delete-confirm.blade.php   — Delete confirmation modal
[ ] partials/loading-spinner.blade.php  — Loading indicator
```

#### Task 4.5.5: Update Views Existing
```
[ ] Refactor views yang pakai CSS .card/.info-box/.table → gunakan components
[ ] Update components/alert.blade.php → Tailwind style
[ ] Update components/data-table.blade.php → Tailwind style
[ ] Update components/info-box.blade.php → Tailwind style
```

**DoD Tahap 4.5:**
- ✅ Semua components bisa dipakai dengan `<x-ui.card>`, `<x-form.input>`, dll
- ✅ Dark theme konsisten di seluruh view
- ✅ Responsive di mobile
- ✅ Micro-interaction (hover, transition, loading state)

---

### TAHAP 5: Plugin Scaffold & ETL [RENDAH]
**Estimasi:** 2-3 sesi | **Dokumen sumber:** DEV_DOCS-042, DEV_DOCS-047

**Tujuan:** Melengkapi 8 plugin scaffold dan ETL pipeline.

#### Task 5.1: Plugin Scaffold (8 Plugin)
```
[ ] AbsensiGuru  — migration, model, controller minimal
[ ] Rapor         — migration, model, controller minimal
[ ] Spp           — migration, model, controller minimal
[ ] Ppdb          — migration, model, controller minimal
[ ] Ekstrakurikuler — migration, model, controller minimal
[ ] Bk            — migration, model, controller minimal
[ ] Perpustakaan  — migration, model, controller minimal
[ ] Inventaris    — migration, model, controller minimal
```

#### Task 5.2: ETL Pipeline
```
[ ] Buat MigrateLegacyDataCommand
[ ] Mapping tabel sisfokol_v7 (legacy) → sisfokol_laravel (baru)
[ ] Implementasi 20 step migrasi sesuai DEV_DOCS-011
[ ] Test: jalankan ETL → data legacy muncul di sistem baru
```

**DoD Tahap 5:**
- ✅ 8 plugin minimal bisa di-activate per tenant
- ✅ ETL berhasil migrasi data legacy

---

### TAHAP 6: Testing & Deployment [AKHIR]
**Estimasi:** 1-2 sesi | **Dokumen sumber:** DEV_DOCS-012 (Epic 12)

**Tujuan:** Memastikan kualitas dan kesiapan deploy.

#### Task 6.1: Integration Testing
```
[ ] Test alur lengkap: Login → Input Siswa → Nilai → Rapor → PDF
[ ] Test multi-tenant: 2 sekolah, data terisolasi
[ ] Test impersonation: SuperAdmin → Kepala Sekolah → Guru
[ ] Test plugin: activate Kurikulum → input CP/TP → muncul di nilai
```

#### Task 6.2: Performance & Security
```
[ ] Audit query N+1 (telescope/debugbar)
[ ] Pastikan semua route punya middleware auth
[ ] Pastikan API rate limiting aktif
[ ] Test SQL injection pada input form
```

#### Task 6.3: Deployment Preparation
```
[ ] Buat .env.production
[ ] Konfigurasi queue worker
[ ] Konfigurasi scheduler (Laravel Horizon atau cron)
[ ] Buat deployment script
[ ] Dokumentasi SOP operasional
```

---

## 📋 DOKUMEN REFERENSI

| Dokumen | Topik | Status |
|---------|-------|--------|
| DEV_DOCS-012 | Master Implementation Plan | ✅ Sumber kebenaran |
| DEV_DOCS-045 | Recovery Plan Tahap 1 | 📋 Terintegrasi ke Tahap 1 |
| DEV_DOCS-046 | Implementation Plan Tahap 2 | 📋 Terintegrasi ke Tahap 4 |
| DEV_DOCS-047 | Implementation Plan Tahap 3 | 📋 Terintegrasi ke Tahap 5 |
| DEV_DOCS-049 | Fix Epic 1 | 📋 Terintegrasi ke Tahap 1-2 |
| DEV_DOCS-050 | Sprint Epic 6 | 📋 Terintegrasi ke Tahap 2 |
| DEV_DOCS-051 | Audit API + Rencana 3 Fase | 📋 Terintegrasi ke Tahap 4 |
| DEV_DOCS-052 | Konsolidasi Dokumen 040-050 | ✅ Referensi klasifikasi |

---

## ⚠️ ATURAN EKSEKUSI

1. **Backup dulu** sebelum setiap tahap → `backups/<tipe>/<nama>.bak_YYYYMMDD`
2. **php83** untuk SEMUA artisan & composer command
3. **Test setelah setiap task** — jangan lanjut jika test merah
4. **Surgical changes only** — jangan refactor yang tidak rusak
5. **Tulis DEV_DOCS baru** jika ada keputusan penting atau sesi panjang
6. **No new audit docs** — fase audit sudah ditutup (DEV_DOCS-052)

---

## 📞 KONTEKS TEKNIS

```
Project root  : D:\laragon\www\sisfokolv7\
Laravel app   : D:\laragon\www\sisfokolv7\sisfokol-laravel\
DB target     : sisfokol_laravel (MySQL, InnoDB, utf8mb4_unicode_ci)
DB legacy     : sisfokol_v7 (MySQL, MyISAM — READ-ONLY, ETL source)
PHP           : php83 (PHP 8.3.31, WAJIB)
Composer      : php83 D:\composer\composer.phar <command>
Host          : http://sisfokol-laravel.test
```

---

## 🔄 CARA MENGGUNAKAN DOKUMEN INI

1. **Baca dokumen ini** sebelum memulai sesi eksekusi
2. **Pilih tahap** yang akan dieksekusi (mulai dari Tahap 1)
3. **Centang task** satu per satu setelah selesai
4. **Jalankan test** setelah setiap tahap
5. **Update status** di tabel atas setelah tahap selesai
6. **Tulis DEV_DOCS-054+** sebagai laporan hasil eksekusi

---

*Dokumen ini dibuat oleh ZCode berdasarkan konsolidasi DEV_DOCS 030-052.*
*Target: Eksekusi dimulai dari Tahap 1 (Unifikasi Model).*
