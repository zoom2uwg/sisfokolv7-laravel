# Design Spec: Hybrid Crudlfix + Livewire

- **Tanggal:** 2026-06-26
- **Status:** Draft
- **Penulis:** ZCode (berdasarkan brainstorming session)
- **Konteks:** ADR-011, Crudlfix Library

---

## Ringkasan Eksekutif

Mengupgrade Crudlfix Library dari Blade SSR + Alpine.js ke hybrid Livewire untuk semua operasi CRUD (form, tabel, modal). Backend logic (Crudlfix trait) tidak berubah — hanya view layer yang menggunakan Livewire components untuk real-time validation, dynamic interactions, dan better UX.

---

## Konteks & Motivasi

### Kondisi Saat Ini

| Aspek | Teknologi |
|-------|-----------|
| Framework | Laravel 11.31 |
| Rendering | Blade SSR |
| CSS | Tailwind CSS 3.4 (CDN + Vite) |
| JS | Alpine.js 3.x (CDN) |
| Build | Vite 6 |
| CRUD Library | Custom "Crudlfix" trait (`app/Support/Crudlfix/`) |
| Livewire | Tidak terinstall |

### Masalah dengan Pendekatan Saat Ini

1. **No real-time validation** — Error muncul setelah submit form (full page reload)
2. **Page reload untuk setiap interaksi** — Search, sort, filter, pagination semua reload
3. **UX kurang smooth** — Loading state tidak optimal, tidak ada optimistic UI
4. **Duplikasi kode di views** — Setiap CRUD view menulis ulang table/form markup

### Tujuan

1. Real-time validation di form (error saat mengetik)
2. Search, sort, filter, pagination tanpa page reload
3. Better loading states dan UX
4. Reusable base components untuk semua CRUD
5. Minimal disruption ke backend (Crudlfix trait tidak berubah)
6. Incremental migration (bisa satu per satu controller)

---

## Arsitektur

### Overview

```
┌─────────────────────────────────────────────────────────┐
│                    Laravel Application                    │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────┐    ┌──────────────────────────────┐   │
│  │  Controller   │    │     Livewire Components      │   │
│  │  (Crudlfix)   │───▶│                              │   │
│  │              │    │  ┌─────────────────────────┐  │   │
│  │  - crudlfix()│    │  │   CrudlfixPage (main)   │  │   │
│  │  - config    │    │  │   - Orchestrates CRUD   │  │   │
│  │              │    │  │   - Reads CrudlfixConfig│  │   │
│  └──────────────┘    │  └───────────┬─────────────┘  │   │
│                      │              │                 │   │
│  ┌──────────────┐    │  ┌───────────▼─────────────┐  │   │
│  │ CrudlfixConfig│    │  │   CrudlfixTable         │  │   │
│  │ (unchanged)   │◀──│  │   - Search (realtime)   │  │   │
│  │              │    │  │   - Sort columns        │  │   │
│  │  - model     │    │  │   - Filters             │  │   │
│  │  - search    │    │  │   - Pagination          │  │   │
│  │  - rules     │    │  │   - Bulk select         │  │   │
│  │  - filters   │    │  │   - Export              │  │   │
│  └──────────────┘    │  └─────────────────────────┘  │   │
│                      │                                │   │
│                      │  ┌─────────────────────────┐  │   │
│                      │  │   CrudlfixForm           │  │   │
│                      │  │   - Real-time validation │  │   │
│                      │  │   - Dynamic fields       │  │   │
│                      │  │   - Cascade selects      │  │   │
│                      │  │   - File upload          │  │   │
│                      │  └─────────────────────────┘  │   │
│                      │                                │   │
│                      │  ┌─────────────────────────┐  │   │
│                      │  │   CrudlfixModal          │  │   │
│                      │  │   - Delete confirmation  │  │   │
│                      │  │   - Bulk actions         │  │   │
│                      │  └─────────────────────────┘  │   │
│                      └──────────────────────────────┘   │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Key Design Decisions

1. **CrudlfixConfig tetap single source of truth** — Livewire components hanya membaca config
2. **Backend tidak berubah** — Crudlfix trait, CrudlfixConfig, CrudlfixView tetap sama
3. **Livewire sebagai view layer** — Menggantikan Blade views, bukan backend logic
4. **Alpine.js tetap dipakai** — Untuk micro-interactions di dalam Livewire views
5. **Incremental migration** — Bisa pindah satu controller per waktu

---

## Komponen Detail

### 1. CrudlfixPage — Orchestrator Component

**File:** `app/Livewire/Crudlfix/CrudlfixPage.php`

**Responsibility:** Mengorchestrasi seluruh operasi CRUD. Menjadi entry point untuk Livewire rendering.

**Properties:**
```php
public CrudlfixConfig $config;
public string $viewPath;
public string $mode = 'index'; // index|create|edit|show
public ?int $editId = null;
public bool $showDeleteModal = false;
public ?int $deleteId = null;
public string $search = '';
public array $filters = [];
public string $sort = '';
public string $direction = 'asc';
public int $page = 1;
```

**Methods:**
```php
mount(string $controller, string $action = 'index')  // Resolve controller, build config
render()                                               // Return view based on $mode
setMode(string $mode, ?int $id = null)                // Switch between index/create/edit/show
confirmDelete(int $id)                                 // Show delete confirmation modal
executeDelete()                                        // Execute delete action
export()                                               // Trigger CSV export
```

**Events:**
- Listens: `refreshPage`, `setMode`, `confirmDelete`
- Emits: `modeChanged`, `dataRefreshed`

---

### 2. CrudlfixTable — Data Table Component

**File:** `app/Livewire/Crudlfix/CrudlfixTable.php`

**Responsibility:** Render data table dengan search, sort, filter, pagination, bulk actions.

**Properties:**
```php
public CrudlfixConfig $config;
public string $search = '';           // Debounced 300ms
public string $sortField;             // Default from config
public string $sortDirection = 'asc';
public array $activeFilters = [];
public int $perPage;                  // From config
public int $currentPage = 1;
public array $selected = [];
public bool $selectAll = false;
```

**Computed Properties:**
```php
getRowsProperty()     // Query with search, filters, sort, pagination
getTotalProperty()    // Total count for pagination
getPagesProperty()    // Page numbers array
```

**Methods:**
```php
sortBy(string $field)                    // Toggle sort direction
applyFilter(string $key, $value)         // Apply filter
clearFilter(string $key)                 // Remove filter
clearAllFilters()                        // Clear all filters
export()                                 // Trigger CSV export
bulkDelete()                             // Delete selected rows
toggleSelectAll()                        // Toggle select all
updatedSearch()                          // Reset page on search change
```

---

### 3. CrudlfixForm — Form Component

**File:** `app/Livewire/Crudlfix/CrudlfixForm.php`

**Responsibility:** Render form dengan real-time validation, dynamic fields, cascade selects.

**Properties:**
```php
public CrudlfixConfig $config;
public array $data = [];
public array $errors = [];
public bool $isEdit = false;
public ?int $editId = null;
public array $viewData = [];          // Extra data for selects, etc.
```

**Methods:**
```php
mount(array $data = [], bool $isEdit = false, ?int $editId = null)
updated($field)                       // Real-time single field validation
updatedCascadeField($value, $field)   // Trigger cascade refresh
save()                                // Full validation + save (store/update)
resetForm()                           // Reset form data
```

**Hooks Support:**
```php
// Before save
if (method_exists($this->controller, 'beforeStore')) {
    $this->controller->beforeStore($this->data);
}

// After save
if (method_exists($this->controller, 'afterStore')) {
    $this->controller->afterStore($model, $this->data);
}
```

---

### 4. CrudlfixModal — Confirmation Modal

**File:** `app/Livewire/Crudlfix/CrudlfixModal.php`

**Responsibility:** Render confirmation modal untuk delete dan bulk actions.

**Properties:**
```php
public bool $show = false;
public string $type = 'delete';       // delete|bulk-delete|custom
public string $title = '';
public string $message = '';
public ?int $targetId = null;
public string $confirmText = 'Hapus';
public string $cancelText = 'Batal';
```

**Methods:**
```php
open(string $type, ?int $id = null, string $title = '', string $message = '')
confirm()                             // Execute action, emit event
cancel()                              // Close modal
```

---

## Integrasi dengan Controller

### Controller Pattern (TIDAK BERUBAH)

```php
class KelasController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => Kelas::class,
            'view'       => 'academic.kelas',
            'route'      => 'academic.kelas',
            'authorize'  => 'kelas',
            'search'     => ['nama'],
            'with'       => ['waliKelas', 'branch'],
            'rules'      => [
                'nama' => 'required|string|max:255',
                'tingkat' => 'required|integer|min:1|max:12',
            ],
            'viewData'   => [
                'waliKelas' => Guru::pluck('nama', 'id'),
                'tingkatOptions' => range(1, 12),
            ],
        ];
    }
}
```

### View Migration

**Sebelum (Blade biasa):**
```blade
{{-- resources/views/academic/kelas/index.blade.php --}}
@extends('layouts.app')
@section('content')
    <x-crudlfix.data-table :config="$config" :rows="$rows" />
@endsection
```

**Sesudah (Livewire component):**
```blade
{{-- resources/views/academic/kelas/index.blade.php --}}
@extends('layouts.app')
@section('content')
    @livewire('crudlfix.page', [
        'controller' => \App\Modules\Academic\Controllers\KelasController::class,
        'action' => 'index'
    ])
@endsection
```

**Atau full-page Livewire (lebih simpel):**
```php
// routes/web.php atau module routes.php
Route::get('/academic/kelas', \App\Livewire\Crudlfix\CrudlfixPage::class)
    ->defaults('controller', KelasController::class);
```

### Crudlfix Trait — Tambahan Method

```php
// app/Support/Crudlfix/Crudlfix.php
trait Crudlfix
{
    // ... existing methods ...
    
    /**
     * Get CrudlfixConfig for Livewire components.
     */
    public function getCrudlfixConfig(): CrudlfixConfig
    {
        return CrudlfixConfig::make($this->crudlfix());
    }
    
    /**
     * Get data for Livewire table (AJAX/Livewire polling).
     */
    public function getCrudlfixData(array $params = []): array
    {
        $config = $this->getCrudlfixConfig();
        // Return paginated, filtered, sorted data
        // Reuse existing index() logic
    }
}
```

---

## File Structure Akhir

```
app/
├── Livewire/
│   └── Crudlfix/
│       ├── CrudlfixPage.php              ← Main orchestrator
│       ├── CrudlfixTable.php             ← Data table
│       ├── CrudlfixForm.php              ← Form component
│       ├── CrudlfixModal.php             ← Confirmation modal
│       └── Traits/
│           ├── HasCrudlfixTable.php      ← Table logic trait
│           ├── HasCrudlfixForm.php       ← Form logic trait
│           └── HasCrudlfixActions.php    ← CRUD actions trait
│
├── Support/
│   └── Crudlfix/
│       ├── Crudlfix.php                  ← Trait (minor additions)
│       ├── CrudlfixConfig.php            ← Unchanged
│       └── CrudlfixView.php             ← Unchanged
│
resources/
├── views/
│   ├── livewire/
│   │   └── crudlfix/
│   │       ├── page.blade.php            ← Main page template
│   │       ├── table.blade.php           ← Table template
│   │       ├── form.blade.php            ← Form template
│   │       └── modal.blade.php           ← Modal template
│   │
│   └── components/
│       └── crudlfix/                      ← Existing (still used)
│           ├── data-table.blade.php
│           ├── cascade-select.blade.php
│           └── search-select.blade.php
```

---

## Migration Path

### Phase 1: Foundation (Minggu 1)

**Hari 1-2: Install & Setup**
- `composer require livewire/livewire`
- Publish Livewire config & assets
- Update `vite.config.js` untuk Livewire assets
- Test basic Livewire component di existing layout

**Hari 3-5: Base Components**
- `CrudlfixPage` (orchestrator)
- `CrudlfixTable` (data table)
- `CrudlfixForm` (form with validation)
- `CrudlfixModal` (confirmation)
- Unit test untuk base components

### Phase 2: Pilot Migration (Minggu 2)

**Hari 1-2: Migrate 1 controller (KelasController)**
- Convert index view → Livewire
- Convert create/edit views → Livewire
- Test semua fitur (search, sort, filter, CRUD)
- Fix issues

**Hari 3-5: Migrate 3-4 controllers**
- `SiswaController`
- `GuruController`
- `MapelController`
- Validate pattern works across different configs

### Phase 3: Bulk Migration (Minggu 3)

**Hari 1-3: Migrate sisa 15 controllers**
- Academic modules (5 controllers)
- Admin controllers (6 controllers)
- Finance controllers (2 controllers)
- Presence controllers (1 controller)
- Plugin controllers (1 controller)

**Hari 4-5: Testing & Polish**
- Integration test semua CRUD
- Performance testing
- Fix edge cases
- Update dokumentasi

---

## Daftar Controller yang Akan Dimigrasi

| # | Controller | Module | Status |
|---|-----------|--------|--------|
| 1 | `KelasController` | Academic | Pilot |
| 2 | `SiswaController` | Academic | Pilot |
| 3 | `GuruController` | Academic | Pilot |
| 4 | `MapelController` | Academic | Pilot |
| 5 | `MapelJenisController` | Academic | Phase 3 |
| 6 | `SemesterController` | Academic | Phase 3 |
| 7 | `TahunAjaranController` | Academic | Phase 3 |
| 8 | `OrangTuaController` | Academic | Phase 3 |
| 9 | `KelasSiswaController` | Academic | Phase 3 |
| 10 | `UserController` | Admin | Phase 3 |
| 11 | `ClassroomController` | Admin | Phase 3 |
| 12 | `AcademicYearController` | Admin | Phase 3 |
| 13 | `SubjectController` | Admin | Phase 3 |
| 14 | `ExtracurricularController` | Admin | Phase 3 |
| 15 | `AttendanceTimeController` | Admin | Phase 3 |
| 16 | `ScheduleController` | Admin | Phase 3 |
| 17 | `ItemPembayaranController` | Finance | Phase 3 |
| 18 | `TabunganSiswaController` | Finance | Phase 3 |
| 19 | `AbsensiController` | Presence | Phase 3 |

---

## Risk Mitigation

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Livewire too heavy for shared hosting | High | Phase 1 testing validates performance; can optimize with lazy loading, pagination limits |
| Breaking existing CRUD during migration | High | Incremental migration — each controller tested individually; existing Blade views kept as fallback |
| Complex form fields (cascade, search-select) | Medium | Reuse existing Alpine.js components inside Livewire views; Alpine works natively with Livewire |
| Livewire + Alpine.js conflict | Low | Both work together natively; Alpine is included in Livewire v3 |
| Learning curve for team | Medium | Documentation + pilot phase allows gradual adoption |
| Increased server load | Medium | Livewire v3 has wire:model.lazy, debounce, and efficient diffing |

---

## Trade-off Analysis

### Keuntungan

1. **Real-time validation** — Error muncul saat user mengetik, bukan setelah submit
2. **No page reload** — Search, sort, filter, pagination tanpa reload
3. **Better UX** — Loading states, optimistic UI, smooth transitions
4. **Minimal backend change** — Crudlfix trait tetap sama
5. **Incremental** — Bisa migrasi satu per satu, tidak harus semua sekaligus
6. **Reusable** — Base components dipakai di semua CRUD

### Trade-off

1. **+2MB dependency** — Livewire package
2. **Server load meningkat** — Setiap interaksi = HTTP request (tapi Livewire v3 sudah optimasi)
3. **Learning curve** — Tim perlu belajar Livewire pattern
4. **~3 minggu implementasi** — Dari foundation sampai migrasi semua controller

---

## Dependencies

### Composer
```json
{
    "require": {
        "livewire/livewire": "^3.0"
    }
}
```

### NPM
Tidak ada dependency baru — Livewire v3 sudah include Alpine.js.

### Config Changes
- `config/livewire.php` — Publish dan sesuaikan (class namespace, view path)
- `vite.config.js` — Tambah Livewire assets jika diperlukan

---

## Referensi

- **ADR-011** — UI Architecture: Blade SSR + Alpine.js
- **Crudlfix Library** — `app/Support/Crudlfix/` (Crudlfix.php, CrudlfixConfig.php, CrudlfixView.php)
- **Livewire v3 Documentation** — https://livewire.laravel.com/docs
- **Existing Controllers** — 19 controllers menggunakan Crudlfix trait

---

## Acceptance Criteria

1. ✅ Livewire v3 terinstall dan terkonfigurasi
2. ✅ Base components (Page, Table, Form, Modal) berfungsi
3. ✅ Real-time validation bekerja di form
4. ✅ Search, sort, filter, pagination tanpa page reload
5. ✅ Semua 19 controller berhasil dimigrasi
6. ✅ Existing Crudlfix trait tidak berubah (backward compatible)
7. ✅ Alpine.js micro-interactions tetap berfungsi di dalam Livewire views
8. ✅ Performance acceptable (response time < 500ms untuk table operations)
9. ✅ Unit test untuk base components
10. ✅ Documentation updated

---

*Spec ini perlu di-review oleh user sebelum lanjut ke implementation plan.*
