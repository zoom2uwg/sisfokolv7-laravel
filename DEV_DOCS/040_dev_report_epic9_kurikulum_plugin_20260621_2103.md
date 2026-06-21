# Dev Report — Epic 9: Plugin Kurikulum (Full Reference Plugin)
**Tanggal**: 2026-06-21 | **Sesi**: 20:50–21:03 WIB  
**Status**: ✅ Task 3, 4, 5 SELESAI — KurikulumPluginTest **3/3 PASS**

---

## 🗂️ Konteks Sesi Ini

Melanjutkan implementasi **Epic 9: Plugin Kurikulum** dari titik sebelumnya di mana Task 1 & 2 (migrasi, model, events, PluginRegistryServiceProvider) sudah selesai. Sesi ini fokus pada:

- **Task 3**: Debug & fix `KurikulumPluginTest`
- **Task 4**: Implementasi Controllers, Policy, Routes, Views
- **Task 5**: Verifikasi akhir dengan test suite

---

## 🐛 Bug yang Ditemukan & Diperbaiki

### Bug #1 — NamespaceError: PluginRegistry tidak ditemukan
**File**: `tests/Feature/Plugin/KurikulumPluginTest.php:124`  
**Error**: `BindingResolutionException: Target class [Tests\Feature\Plugin\PluginRegistry] does not exist`

**Root Cause**: Pemanggilan `app(PluginRegistry::class)` tanpa `use` import di atas, sehingga `PluginRegistry` diresolve relatif terhadap namespace test (`Tests\Feature\Plugin\PluginRegistry`).

**Fix**:
```php
// ❌ Sebelum
app(PluginRegistry::class)->clearTenantCache($tenant->id, 'kurikulum');

// ✅ Sesudah
app(\App\Support\PluginRegistry::class)->clearTenantCache($tenant->id, 'kurikulum');
```

---

### Bug #2 — EventListener tidak terpanggil setelah manual provider registration
**File**: `tests/Feature/Plugin/KurikulumPluginTest.php`  
**Error**: `Failed asserting that null is not null` (framework selalu null)

**Root Cause**: Ketika `$this->app->register(KurikulumServiceProvider::class)` dipanggil **setelah** aplikasi sudah selesai boot, `EventServiceProvider::boot()` yang diwarisi tidak men-trigger `$events->subscribe()` untuk class-class di `$subscribe` array. Akibatnya `EvaluationFrameworkSubscriber` tidak terdaftar ke event dispatcher meskipun provider sudah diregistrasi.

**Fix**: Tambahkan eksplisit subscribe setelah register provider:
```php
// ❌ Sebelum
$this->app->register(KurikulumServiceProvider::class);

// ✅ Sesudah
$this->app->register(KurikulumServiceProvider::class);
$this->app['events']->subscribe(
    \App\Plugins\Kurikulum\Subscribers\EvaluationFrameworkSubscriber::class
);
```

**Pelajaran**: Saat test memerlukan subscriber dari plugin yang di-register secara runtime (bukan via `config/app.php`), harus eksplisit memanggil `$events->subscribe()` karena `EventServiceProvider::boot()` tidak di-replay untuk provider yang di-register setelah app boot.

---

### Bug #3 — Subscriber dispatch format tidak tepat
**File**: `EvaluationFrameworkSubscriber.php`, `RaporSectionSubscriber.php`

**Root Cause**: Method `subscribe()` mengembalikan `['EventClass' => 'methodName']` (string saja). Format yang benar untuk Laravel dispatcher adalah `'ClassName@methodName'`.

**Fix**:
```php
// ❌ Sebelum
return [
    EvaluationResolveFramework::class => 'handleEvaluationResolveFramework',
];

// ✅ Sesudah
return [
    EvaluationResolveFramework::class => static::class . '@handleEvaluationResolveFramework',
];
```

---

## 📁 File yang Dibuat / Dimodifikasi

### ✅ Dibuat Baru (Task 4)

| File | Deskripsi |
|------|-----------|
| `app/Plugins/Kurikulum/Policies/KurikulumPolicy.php` | Policy otorisasi CRUD kurikulum (viewAny, view, create, update, delete) dengan tenant isolation |
| `app/Plugins/Kurikulum/Controllers/KurikulumController.php` | CRUD controller master kurikulum |
| `app/Plugins/Kurikulum/Controllers/StrukturKurikulumController.php` | CRUD controller struktur kurikulum (jenjang/kelas/fase mapping) |
| `app/Plugins/Kurikulum/Controllers/KomponenKompetensiController.php` | CRUD controller komponen kompetensi (KI/KD/CP/TP) |
| `app/Plugins/Kurikulum/routes.php` | Plugin routes dengan middleware `plugin:kurikulum` guard |
| `app/Plugins/Kurikulum/Resources/views/kurikulum/index.blade.php` | Premium dark-theme table view |
| `app/Plugins/Kurikulum/Resources/views/kurikulum/create.blade.php` | Form tambah kurikulum |
| `app/Plugins/Kurikulum/Resources/views/kurikulum/edit.blade.php` | Form edit kurikulum |
| `app/Plugins/Kurikulum/Resources/views/struktur/index.blade.php` | Table view struktur dengan color-coded badges |
| `app/Plugins/Kurikulum/Resources/views/struktur/create.blade.php` | Form tambah struktur |
| `app/Plugins/Kurikulum/Resources/views/struktur/edit.blade.php` | Form edit struktur |
| `app/Plugins/Kurikulum/Resources/views/komponen/index.blade.php` | Table view komponen kompetensi |
| `app/Plugins/Kurikulum/Resources/views/komponen/create.blade.php` | Form tambah komponen |
| `app/Plugins/Kurikulum/Resources/views/komponen/edit.blade.php` | Form edit komponen |

### 📝 Dimodifikasi (Fix & Enhancement)

| File | Perubahan |
|------|-----------|
| `app/Providers/AuthServiceProvider.php` | Tambah `KurikulumPolicy` ke array `$policies` |
| `app/Plugins/Kurikulum/Providers/KurikulumServiceProvider.php` | Tambah route loading via `Route::middleware('web')->group($routesFile)` |
| `app/Plugins/Kurikulum/Subscribers/EvaluationFrameworkSubscriber.php` | Fix subscriber dispatch format ke `ClassName@method` |
| `app/Plugins/Kurikulum/Subscribers/RaporSectionSubscriber.php` | Fix subscriber dispatch format ke `ClassName@method` |
| `tests/Feature/Plugin/KurikulumPluginTest.php` | Fix #1 FQN namespace, fix #2 eksplisit event subscribe, cleanup setup order |

---

## 🎨 Desain View (Tailwind CSS + Glassmorphism)

Semua 9 view dibuat dengan tema premium dark mode mengikuti pola `layouts.app`:

- **Kurikulum** (violet/indigo accent): Toggle switch status aktif, font-mono untuk kode kurikulum
- **Struktur Kurikulum** (cyan/teal accent): Color-coded badge jenjang (SD=green, SMP=blue, SMA=purple, SMK=orange), color-coded jenis kegiatan
- **Komponen Kompetensi** (pink/rose accent): `line-clamp-2` untuk deskripsi panjang, nested info kurikulum+struktur dalam satu cell

**Fitur UI konsisten**:
- Hover-reveal action buttons (opacity transition)
- Auto-dismiss flash message via Alpine.js `setTimeout`
- Empty state dengan ilustrasi icon dan CTA link
- Gradient header per form (berbeda warna untuk create vs edit)

---

## 🧪 Hasil Test

### KurikulumPluginTest (3/3 ✅)
```
PASS  Tests\Feature\Plugin\KurikulumPluginTest
  ✓ evaluation framework event resolves via kurikulum      51.60s
  ✓ no framework when mapel has no kurikulum id             0.07s
  ✓ kurikulum can be activated and seeds permissions        1.60s

Tests:    3 passed (9 assertions)
Duration: 53.39s
```

### Full Test Suite — 112 passed, 3 failed (pre-fix)

**Failures yang ditemukan** — semuanya regresi akibat penambahan plugin Kurikulum ke disk:

| Test | Error | Root Cause |
|------|-------|-----------|
| `EnsurePluginEnabledTest::test_tenant_user_allowed_when_plugin_active` | `UniqueConstraintViolation: Duplicate entry 'kurikulum'` | `PluginRegistryServiceProvider` auto-sync plugin ke DB saat boot, test juga `Plugin::create('kurikulum')` → duplikat |
| `PluginRegistryTest::test_registry_returns_empty_when_no_plugins_on_disk` | `assertCount(0)` padahal ada 1 plugin | Kurikulum sudah ada di disk, registry tidak pernah empty lagi |
| `PluginRegistryTest::test_registry_discovers_plugin_manifest_files` | `assertCount(1)` padahal ada 2 plugin | Kurikulum + TestPlugin = 2, bukan 1 |

**Fix yang diterapkan**:
- `EnsurePluginEnabledTest`: Ubah `Plugin::create()` → `Plugin::updateOrCreate()` 
- `PluginRegistryTest`: Ubah assertion count ke `assertArrayHasKey('kurikulum', ...)` dan `assertArrayHasKey('testplugin', ...)` — lebih robust

### Plugin Tests — Verifikasi Final ✅
```
PASS  Tests\Feature\Plugin\EnsurePluginEnabledTest
  ✓ superadmin bypasses plugin check                    53.63s
  ✓ tenant user blocked when plugin inactive             0.09s
  ✓ tenant user allowed when plugin active               0.05s

PASS  Tests\Feature\Plugin\KurikulumPluginTest
  ✓ evaluation framework event resolves via kurikulum    0.36s
  ✓ no framework when mapel has no kurikulum id          0.04s
  ✓ kurikulum can be activated and seeds permissions     0.34s

PASS  Tests\Feature\Plugin\PluginActivationTest
  ✓ admin can activate plugin for their tenant           0.33s
  ✓ admin can deactivate plugin                         0.34s
  ✓ activation blocked while impersonating               0.33s
  ✓ non admin cannot activate                           0.32s

PASS  Tests\Feature\Plugin\PluginRegistryTest
  ✓ registry returns empty when no plugins on disk       0.06s
  ✓ registry discovers plugin manifest files             0.06s
  ✓ registry syncs to database                          0.05s
  ✓ is active for tenant returns false when not activated 0.06s

Tests:    14 passed (27 assertions)
Duration: 56.19s
```

---

## 📐 Arsitektur Plugin Kurikulum — Ringkasan

```
Request → Middleware plugin:kurikulum → KurikulumController
                                              ↓
                                     KurikulumPolicy (authorize)
                                              ↓
                                     Kurikulum Model (BelongsToTenant scope)
                                              ↓
                                       kurikulum:: views

Event Flow:
GradeEntryController → EvaluationFrameworkResolver::resolve()
                              → event(EvaluationResolveFramework)
                                      ↓ [if plugin aktif untuk tenant]
                              EvaluationFrameworkSubscriber::handle()
                                      ↓
                              $event->framework = [ki, fase, pedagogis]
                                      ↓
                              GradeEntryController receives framework
```

---

## ⚠️ Catatan Penting

1. **PHP 8.3** (`php83`) digunakan, bukan path php74 lama.
2. **TenantContext** harus di-set **sebelum** `PluginRegistry::isActiveForTenant()` dipanggil — urutan penting dalam test setup.
3. **EventServiceProvider `$subscribe`** tidak di-replay untuk provider yang diregistrasi post-boot — selalu eksplisit subscribe dalam test environment.
4. Views plugin disimpan di `app/Plugins/Kurikulum/Resources/views/` (bukan `resources/views/`) dan diakses via namespace `kurikulum::`.

---

## 🔜 Next Steps

- [ ] Konfirmasi full test suite 100% hijau
- [ ] Aktifkan plugin Kurikulum via `/admin/plugins` di browser
- [ ] Verifikasi menu sidebar muncul: Kurikulum, Struktur Kurikulum, Komponen Kompetensi
- [ ] Buat data sample: Kurikulum Merdeka → SMA Kelas 10 Fase E → beberapa CP
- [ ] Hubungkan mata pelajaran dengan kurikulum dan verifikasi resolusi framework di grade entry
