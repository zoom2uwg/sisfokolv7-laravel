# DEV_DOCS-062: Verifikasi Mendalam Epic 9 — Kurikulum Plugin

- **Tanggal:** 2026-06-25
- **Dibuat oleh:** OpenCode Agent (Anomaly) — model DeepseekV4Flash
- **Proyek:** SISFOKOL v7.00 → Laravel 11 (`sisfokol-laravel/`)
- **Metode:** Baca seluruh kode + jalankan test + verifikasi integritas data

---

## Ringkasan Eksekutif

**Verdict: ⚠️ LULUS DENGAN 2 BUG**

| Aspek | Status |
|-------|:------:|
| File fisik | ✅ 28/28 (100%) |
| Kode nyata | ✅ 27/28 file kode produksi (1 placeholder) |
| Test suite | ✅ 14/14 PASS (27 assertions, 58.27 detik) |
| No hallucination | ✅ Tidak ada klaim palsu |
| No mockup data | ✅ Tidak ada data fake/seed dalam kode |
| No friction | ✅ Test berjalan tanpa error |
| **Bug: enum mismatch** | ❌ `jenis_kegiatan` enum tidak sinkron |
| **Bug: rapor placeholder** | ❌ `RaporSectionSubscriber` return HTML statis |

---

## 1. Verifikasi File — 28/28 ADA

### Plugin Manifest (4 file)
| File | Size | Status |
|------|-----:|:------:|
| `KurikulumPlugin.php` | 1.738 B | ✅ Real |
| `menu.php` | 589 B | ✅ Real |
| `permissions.php` | 239 B | ✅ Real |
| `routes.php` | 2.642 B | ✅ Real |

### Models (3 file)
| File | Size | Lines | Status |
|------|-----:|------:|:------:|
| `Kurikulum.php` | 688 B | 27 | ✅ Real |
| `StrukturKurikulum.php` | 739 B | 27 | ✅ Real |
| `KomponenKompetensi.php` | 574 B | 21 | ✅ Real |

### Controllers (3 file)
| File | Size | Lines | Status |
|------|-----:|------:|:------:|
| `KurikulumController.php` | 2.510 B | 86 | ✅ Real |
| `StrukturKurikulumController.php` | 3.094 B | 96 | ✅ Real |
| `KomponenKompetensiController.php` | 3.265 B | 97 | ✅ Real |

### Subscribers (2 file)
| File | Size | Lines | Status |
|------|-----:|------:|:------:|
| `EvaluationFrameworkSubscriber.php` | 1.918 B | 62 | ✅ Real |
| `RaporSectionSubscriber.php` | 917 B | 30 | ⚠️ Placeholder |

### Supporting (16 file)
| File | Status |
|------|:------:|
| `KurikulumServiceProvider.php` | ✅ Real |
| `KurikulumPolicy.php` | ✅ Real |
| 4 Migrations | ✅ Real |
| 9 Blade views | ✅ Real |

---

## 2. Verifikasi Kode per Komponen

### 2.1 KurikulumPlugin.php — PluginContract Implementation

```
Implementasi 9/9 methods:
- kode() → 'kurikulum'
- nama() → 'Kurikulum'
- versi() → '1.0.0'
- isCore() → false
- dependencies() → []
- providerClass() → KurikulumServiceProvider::class
- permissions() → 2 permissions (kurikulum.view, kurikulum.manage)
- menu() → 3 menu items (Kurikulum, Struktur, Komponen)
- boot(PluginContext) → empty (deferred to ServiceProvider)
```

**Verdict:** ✅ Fully compliant dengan PluginContract. Tidak ada stub.

### 2.2 Models — Eloquent dengan Relasi

**Kurikulum:**
- Traits: `SoftDeletes`, `BelongsToTenant`, `TracksAuditColumns`
- Fillable: `kurikulum_id`, `nama_kurikulum`, `status_aktif`
- Casts: `status_aktif` → boolean
- Relations: `strukturKurikulum()` → HasMany

**StrukturKurikulum:**
- Traits: `BelongsToTenant`, `TracksAuditColumns`
- Fillable: `kurikulum_id`, `jenjang`, `kelas`, `fase`, `jenis_kegiatan`
- Relations: `kurikulum()` → BelongsTo, `komponenKompetensi()` → HasMany

**KomponenKompetensi:**
- Traits: `BelongsToTenant`, `TracksAuditColumns`
- Fillable: `struktur_id`, `kode_kompetensi`, `teks_kompetensi`, `pendekatan_pedagogis`
- Relations: `struktur()` → BelongsTo

**Verdict:** ✅ Semua model real, ada relasi, casts, fillable. Tidak ada placeholder.

### 2.3 Controllers — Full CRUDL

**KurikulumController (6 methods):**
- `index()` — paginated list, authorize viewAny
- `create()` — form view
- `store()` — validate + unique + tenant_id + create
- `edit()` — form view with model
- `update()` — validate + unique exclusion + update
- `destroy()` — authorize delete + soft delete

**StrukturKurikulumController (6 methods):**
- `index()` — with eager load kurikulum
- `create()` — dropdown kurikulumOptions
- `store()` — validate jenjang/kelas/fase/jenis_kegiatan + create
- `edit()` — pre-populated form
- `update()` — validate + update
- `destroy()` — delete

**KomponenKompetensiController (6 methods):**
- `index()` — with eager load struktur.kurikulum
- `create()` — dropdown strukturOptions
- `store()` — validate + create
- `edit()` — pre-populated form
- `update()` — validate + update
- `destroy()` — delete

**Verdict:** ✅ Semua controller real, ada validasi, authorization, tenant scoping. Tidak ada stub.

### 2.4 EvaluationFrameworkSubscriber — Event Listener

**Logic:**
1. Check tenant plugin activation via `PluginRegistry::isActiveForTenant()`
2. Query `Kurikulum::find($mapel->kurikulum_id)`
3. Query `StrukturKurikulum` matching jenjang + kelas
4. Query `KomponenKompetensi::where('struktur_id')` → pluck kode_kompetensi
5. Fill `$event->framework` with: kurikulum name, ki array, fase, pedagogis

**Verdict:** ✅ Real, production-quality event subscriber. Query nyata ke database.

### 2.5 RaporSectionSubscriber — ⚠️ PLACEHOLDER

**Current code (line 20):**
```php
$html = '<p><em>Section Capaian Kompetensi dari plugin Kurikulum.</em></p>';
$event->sections['Capaian Kompetensi'] = $html;
```

**Issue:** Returns hardcoded italic HTML string. Tidak query data siswa, nilai, atau kurikulum untuk compute capaian kompetensi yang sebenarnya.

**Impact:** Rapor akan menampilkan teks placeholder alih-alih capaian kompetensi aktual.

**Verdict:** ⚠️ Infrastructure benar (event wiring, tenant check), tapi business logic belum diimplementasikan.

### 2.6 Routes — 18 CRUD Routes

```php
Route::middleware(['web', 'auth', 'plugin:kurikulum'])
    ->prefix('kurikulum')
    ->name('kurikulum.')
    ->group(function () {
        // Master Kurikulum (6 routes)
        // Struktur Kurikulum (6 routes)
        // Komponen Kompetensi (6 routes)
    });
```

**Verdict:** ✅ 18 routes real, ada middleware `plugin:kurikulum`, naming konsisten.

### 2.7 Blade Views — 9 Files

| View | Lines | Features |
|------|------:|----------|
| `kurikulum/index` | 141 | Paginated table, flash messages, delete confirmation, @can gates |
| `kurikulum/create` | 107 | Form with old(), @error, Alpine toggle |
| `kurikulum/edit` | 90 | Pre-filled form, @method('PUT') |
| `struktur/index` | 147 | Color-coded match() for jenjang/jenis |
| `struktur/create` | 116 | Dropdown from $kurikulumOptions |
| `struktur/edit` | 96 | Pre-populated selects |
| `komponen/index` | 136 | Deep relationship traversal, null-safe operator |
| `komponen/create` | 94 | Dropdown from $strukturOptions |
| `komponen/edit` | 85 | Pre-populated form |

**Verdict:** ✅ Semua view real, ada @csrf, @method, @error, @can, old(), pagination. Tidak ada mockup data.

### 2.8 Migrations — 4 Files

| Migration | Tables/Changes |
|-----------|----------------|
| `000500_create_kurikulum_table` | `kurikulum` — id, tenant_and_audit, kurikulum_id, nama_kurikulum, status_aktif |
| `000501_create_struktur_kurikulum_table` | `struktur_kurikulum` — FK kurikulum, jenjang, kelas, fase, enum jenis_kegiatan |
| `000502_create_komponen_kompetensi_table` | `komponen_kompetensi` — FK struktur, kode_kompetensi, teks_kompetensi, enum pedagogis |
| `000503_add_mapel_kurikulum_fk` | Adds FK `kurikulum_id` on `mapel` table |

**Verdict:** ✅ Semua migration real, ada foreign keys, unique constraints, indexes.

### 2.9 KurikulumPolicy — Authorization

```php
// 5 methods: viewAny, view, create, update, delete
// Super admin bypass (tenant_id === null)
// Permission check: kurikulum.view / kurikulum.manage
// Tenant scoping on view/update/delete
```

**Verdict:** ✅ Real policy dengan multi-tenant authorization.

---

## 3. Test Suite — 14/14 PASS

### Test Results

| Test Class | Methods | Status | Duration |
|-----------|:-------:|:------:|:--------:|
| `PluginRegistryTest` | 4 | ✅ 4 PASS | 0.21s |
| `EnsurePluginEnabledTest` | 3 | ✅ 3 PASS | 51.51s |
| `PluginActivationTest` | 4 | ✅ 4 PASS | 2.71s |
| `KurikulumPluginTest` | 3 | ✅ 3 PASS | 3.59s |
| **TOTAL** | **14** | **✅ 14 PASS** | **58.27s** |

### KurikulumPluginTest Detail

| Test | Assertions | What It Verifies |
|------|:----------:|------------------|
| `test_evaluation_framework_event_resolves_via_kurikulum` | 5 | Event `EvaluationResolveFramework` → subscriber returns framework data (kurikulum name, ki array, fase, pedagogis) |
| `test_no_framework_when_mapel_has_no_kurikulum_id` | 1 | When `mapel.kurikulum_id` is null → framework is null (fallback) |
| `test_kurikulum_can_be_activated_and_seeds_permissions` | 3 | Activation route seeds `kurikulum.view` + `kurikulum.manage` permissions |

**Verdict:** ✅ Test real, adaassertions nyata, tidak ada mockup/test double yang menipu.

---

## 4. Temuan Bug

### Bug 1: `jenis_kegiatan` Enum Mismatch ❌

**Lokasi:**
- Migration `000501` line 18: `enum('jenis_kegiatan', ['intrakurikuler', 'kokurikuler_p5'])`
- Controller `StrukturKurikulumController` line 44, 76: `'in:intrakurikuler,kokurikuler,ekstrakurikuler'`
- Blade views `create.blade.php` line 95-96, `edit.blade.php` line 76-77, `index.blade.php` line 93-94

**Dampak:**
- User pilih "Kokurikuler (P5)" → value `kokurikuler` → MySQL enum reject → **SQL ERROR**
- User pilih "Ekstrakurikuler" → value `ekstrakurikuler` → MySQL enum reject → **SQL ERROR**
- Hanya "Intrakurikuler" yang bisa disimpan成功

**Fix:** Sinkronkan controller + views dengan migration:
- Opsi: `intrakurikuler`, `kokurikuler_p5`, `ekstrakurikuler` (tambah `ekstrakurikuler` ke migration)
- Atau: `intrakurikuler`, `kokurikuler`, `ekstrakurikuler` (ubah migration enum)

### Bug 2: `RaporSectionSubscriber` Placeholder ⚠️

**Lokasi:** `RaporSectionSubscriber.php` line 20

**Dampak:** Rapor menampilkan teks placeholder alih-alih capaian kompetensi aktual.

**Fix:** Implementasi query data siswa + nilai + kurikulum → compute capaian → render HTML.

---

## 5. Integrasi dengan Modul Lain

### Evaluation Module
- `EvaluationFrameworkResolver` dispatches `EvaluationResolveFramework` event
- `EvaluationFrameworkSubscriber` listens and fills `$event->framework`
- ✅ Integration verified via test

### Tenancy
- `BelongsToTenant` trait auto-fills `tenant_id` on creating (line 25-29 of trait)
- `TenantContext` singleton provides current tenant
- ✅ Multi-tenant scoping works correctly

### Plugin Infrastructure (Epic 4)
- `PluginRegistry::isActiveForTenant()` gates subscriber execution
- `EnsurePluginEnabled` middleware protects routes
- `PluginActivationService` handles activation/deactivation
- ✅ Full integration with Epic 4 verified

---

## 6. Kesimpulan

### Apa yang BERFUNGSI:
1. ✅ PluginContract implementation — 9/9 methods
2. ✅ 3 CRUD controllers — full validasi + authorization
3. ✅ 3 Eloquent models — relasi, casts, tenant scoping
4. ✅ EvaluationFrameworkSubscriber — event-driven, query nyata
5. ✅ 18 routes — middleware plugin:kurikulum
6. ✅ 9 Blade views — production-ready, no mockup
7. ✅ 4 migrations — FK, unique, enum
8. ✅ KurikulumPolicy — multi-tenant authorization
9. ✅ Test suite — 14/14 PASS

### Apa yang TIDAK BERFUNGSI:
1. ❌ `jenis_kegiatan` enum mismatch — controller/views tidak sinkron dengan migration
2. ❌ `RaporSectionSubscriber` — return placeholder HTML, bukan computed data

### Rekomendasi Fix:
1. **[KRITIS]** Sinkronkan enum `jenis_kegiatan` di controller + views + migration
2. **[MEDIUM]** Implementasi `RaporSectionSubscriber` dengan query data nyata

---

*Laporan ini digenerate oleh OpenCode Agent (Anomaly) menggunakan model DeepseekV4Flash.*
*Verifikasi dilakukan dengan membaca seluruh 28 file + menjalankan 14 test secara langsung.*
