# CRUDLFIX v2 — Design Spec

**Date:** 2026-06-25
**Status:** Draft
**Scope:** Improve CRUDLFIX library to handle dynamic cascading, search select, and full-featured data tables

---

## 1. Context

### Current State
CRUDLFIX is a custom trait-based CRUD library (534 LOC) used by 21 controllers. It handles basic CRUD operations effectively for simple cases (80% of use cases).

### Problems
1. **No cascading selects** — forms with related data (e.g., tahun ajaran → semester) require all data loaded upfront via `viewData`
2. **No search select** — large dropdowns (100+ items) have no search/filter capability
3. **No client-side table features** — current tables are server-rendered with no sort, search, or view toggle
4. **No live editing** — all edits require full page reload
5. **Limited responsive design** — tables don't adapt to different screen sizes

### Decision
Extend CRUDLFIX with Alpine.js components (consistent with ADR-011: Blade SSR + Alpine.js + Tailwind CSS). No jQuery, no Select2, no DataTables library.

---

## 2. Architecture

### Approach: Extend Existing Trait (Approach A)

```
Controller (config array)
  ↓
CrudlfixConfig (parse new config keys)
  ↓
Crudlfix trait (handle API endpoints + pass data to view)
  ↓
Blade Components (Alpine.js powered)
  ↓
Alpine.js (client-side interactivity)
```

### New Config Keys

```php
// Cascading selects
'cascades' => [
    'parent_field' => [
        'target' => 'child_field',
        'query'  => fn($value) => Model::where('parent_id', $value),
        'value'  => 'id',
        'label'  => 'nama',
    ],
],

// Search selects (Select2 replacement)
'searchSelects' => [
    'field_name' => [
        'query' => fn($q) => Model::where('nama', 'like', "%{$q}%"),
        'value' => 'id',
        'label' => 'nama',
    ],
],

// Data table config
'dataTable' => [
    'variant' => 'standard', // simple | standard | advanced
],
```

### API Endpoint Pattern

```php
// Route registration (manual, per module)
Route::get('api/resource', [Controller::class, 'api'])->name('resource.api');

// Controller method (provided by trait)
public function api(Request $request): JsonResponse
{
    return match($request->query('type')) {
        'cascade' => $this->handleCascade($request),
        'search'  => $this->handleSearchSelect($request),
        'lazy'    => $this->handleLazyLoad($request),
        default   => response()->json(['error' => 'Unknown type'], 400),
    };
}
```

---

## 3. Components

### 3.1 `<x-crudlfix.search-select>` — Select2 Replacement

**Features:**
- AJAX search with debounce
- Keyboard navigation (arrow keys, enter, escape)
- Selected value display
- Clear button
- Loading state
- No results state

**Usage:**
```blade
<x-crudlfix.search-select 
    name="guru_id" 
    url="{{ route('academic.jadwal.api') }}"
    field="guru_id"
    label="Cari guru..."
    :selected="$selectedGuru" />
```

### 3.2 `<x-crudlfix.cascade-select>` — Cascading Select

**Features:**
- Parent → child relationship
- Auto-load child options when parent changes
- Reset child when parent changes
- Loading state
- Event-based communication (`$dispatch`)

**Usage:**
```blade
{{-- Parent --}}
<x-crudlfix.select 
    name="tahun_ajaran_id" 
    :options="$tahunAjarans"
    @change="$dispatch('cascade-tahun_ajaran_id', $event.target.value)" />

{{-- Child (auto-loads when parent changes) --}}
<x-crudlfix.cascade-select 
    name="semester_id" 
    dependsOn="tahun_ajaran_id"
    url="{{ route('academic.jadwal.api') }}"
    field="tahun_ajaran_id"
    label="Pilih semester..." />
```

### 3.3 `<x-crudlfix.data-table>` — Full-Featured DataTable

**3 Tiers:**

| Feature | `simple` | `standard` | `advanced` |
|---------|----------|------------|------------|
| Search | ✅ | ✅ | ✅ |
| Sort columns | ✅ | ✅ | ✅ |
| Pagination | ✅ | ✅ | ✅ |
| View toggle | ❌ | ✅ | ✅ |
| Per page selector | ❌ | ✅ | ✅ |
| Row select (checkbox) | ❌ | ❌ | ✅ |
| Bulk actions | ❌ | ❌ | ✅ |
| Export (CSV/Excel/PDF) | ❌ | ❌ | ✅ |
| Live edit (inline) | ❌ | ❌ | ✅ |
| Nested rows (expand) | ❌ | ❌ | ✅ |
| Custom column buttons | ❌ | ✅ | ✅ |
| Custom cell renderers | ❌ | ✅ | ✅ |
| Row actions | ❌ | ✅ | ✅ |
| Custom row component | ❌ | ❌ | ✅ |
| Per-column filter | ❌ | ✅ | ✅ |
| Column visibility | ❌ | ✅ | ✅ |
| Multi-column sort | ❌ | ❌ | ✅ |
| Fixed header | ❌ | ✅ | ✅ |
| Row grouping | ❌ | ❌ | ✅ |
| ColReorder | ❌ | ❌ | ✅ |
| Virtual scroll | ❌ | ❌ | ✅ |

**Responsive Breakpoints:**

| Breakpoint | Width | Table Behavior |
|------------|-------|----------------|
| Desktop | ≥1024px | Full table with all columns |
| Tablet (landscape) | 768-1023px | Table with horizontal scroll, sticky first column |
| Tablet (portrait) | 640-767px | Card view or collapsed columns |
| Mobile (landscape) | 480-639px | Card view with essential columns |
| Mobile (portrait) | <480px | List view with single column focus |

**Responsive Strategy:**
```blade
{{-- Desktop: Full table --}}
<div class="hidden lg:block">
    <table>...</table>
</div>

{{-- Tablet: Scrollable table with sticky first column --}}
<div class="hidden md:block lg:hidden overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th class="sticky left-0 bg-slate-900 z-10">Name</th>
                {{-- Other columns scroll --}}
            </tr>
        </thead>
    </table>
</div>

{{-- Mobile: Card view --}}
<div class="md:hidden">
    <template x-for="row in paginated">
        <div class="card">...</div>
    </template>
</div>
```

**Cell Renderers (Built-in):**

| Renderer | Output |
|----------|--------|
| `badge` | Colored badge based on value |
| `date` | Formatted date (d/m/Y) |
| `currency` | Rp 1.000.000 |
| `boolean` | ✅ / ❌ |
| `day-icon` | Day with icon |
| `truncate` | Truncated text with tooltip |
| `image` | Thumbnail image |
| `link` | Clickable link |

**Custom Cell Renderer:**
```blade
<x-slot name="renderer-status">
    <span :class="{
        'bg-emerald-500/20 text-emerald-400': row.status === 'aktif',
        'bg-rose-500/20 text-rose-400': row.status === 'nonaktif',
    }" x-text="row.status"></span>
</x-slot>
```

### 3.4 `<x-crudlfix.live-edit>` — Inline Editing

**Features:**
- Click to edit any cell
- Save on Enter or blur
- Cancel on Escape
- Visual feedback (loading, success, error)
- Optimistic UI update

**Usage:**
```blade
<x-crudlfix.data-table 
    :data="$jadwals" 
    :columns="['hari' => 'Hari', 'jam' => 'Jam']"
    :liveEdit="true"
    liveEditUrl="{{ route('academic.jadwal.update') }}" />
```

---

## 4. File Structure

```
sisfokol-laravel/
├── app/
│   ├── Support/
│   │   └── Crudlfix/
│   │       ├── Crudlfix.php              # Core trait (extended)
│   │       ├── CrudlfixConfig.php         # Config object (extended)
│   │       └── CrudlfixView.php           # View helper (legacy, keep for compat)
│   └── Http/
│       └── Controllers/
│           └── Admin/
│               └── ScheduleController.php # Example with new features
├── resources/
│   ├── views/
│   │   ├── components/
│   │   │   └── crudlfix/
│   │   │       ├── data-table.blade.php      # Full-featured DataTable
│   │   │       ├── search-select.blade.php   # Select2 replacement
│   │   │       ├── cascade-select.blade.php  # Cascading select
│   │   │       ├── live-edit.blade.php       # Inline editing
│   │   │       ├── cell-renderers.blade.php  # Built-in renderers
│   │   │       └── select.blade.php          # Standard select
│   │   └── admin/
│   │       └── schedules/
│   │           ├── index.blade.php           # Using data-table
│   │           └── create.blade.php          # Using search-select + cascade
│   └── js/
│       └── crudlfix/
│           ├── cascade.js                    # Cascade Alpine component
│           ├── search-select.js              # Search select Alpine component
│           ├── data-table.js                 # DataTable Alpine component
│           └── live-edit.js                  # Live edit Alpine component
└── routes/
    └── admin.php                             # API routes
```

---

## 5. Implementation Phases

### Phase 1: Core Components (MVP)
1. `<x-crudlfix.select>` — Standard select with Tailwind styling
2. `<x-crudlfix.search-select>` — AJAX search select
3. `<x-crudlfix.cascade-select>` — Cascading select
4. API endpoint handler in Crudlfix trait
5. Example: JadwalController with cascading (tahun ajaran → semester)

### Phase 2: DataTable
1. `<x-crudlfix.data-table>` — Simple variant
2. Standard variant (view toggle, per-column filter, column visibility)
3. Advanced variant (bulk actions, export, live edit, nested rows)
4. Responsive breakpoints (desktop, tablet, mobile)
5. Cell renderers (badge, date, currency, boolean)
6. Example: Replace existing index views

### Phase 3: Advanced Features
1. Multi-column sort
2. Fixed header
3. Row grouping
4. ColReorder (drag & drop)
5. Virtual scroll (for 1000+ rows)
6. Export Excel/PDF (via backend packages)

---

## 6. Migration Strategy

### Backward Compatibility
- Existing controllers continue to work without changes
- New features are opt-in via config keys
- `CrudlfixView.php` kept for legacy compatibility

### Adoption Path
1. **New controllers:** Use new components from start
2. **Existing simple controllers:** No changes needed
3. **Existing complex controllers:** Gradually adopt (e.g., add search-select for large dropdowns)

### Example Migration

```php
// BEFORE (current)
protected function crudlfix(): array {
    return [
        'model'    => Jadwal::class,
        'viewData' => [
            'gurus' => Guru::all(), // Load ALL gurus
        ],
    ];
}

// AFTER (v2)
protected function crudlfix(): array {
    return [
        'model'    => Jadwal::class,
        'searchSelects' => [
            'guru_id' => [
                'query' => fn($q) => Guru::where('nama', 'like', "%{$q}%"),
                'value' => 'id',
                'label' => 'nama',
            ],
        ],
        // viewData for gurus REMOVED — loaded via AJAX
    ];
}
```

---

## 7. Success Criteria

1. **Cascading selects work** — selecting parent auto-loads child options
2. **Search select works** — type to search, select from results
3. **DataTable replaces server-rendered tables** — sort, search, pagination all client-side
4. **Responsive works** — table adapts to desktop, tablet, mobile
5. **Live edit works** — click to edit, save inline
6. **No jQuery dependency** — pure Alpine.js + Tailwind
7. **Backward compatible** — existing controllers still work
8. **Performance** — client-side operations for <1000 rows

---

## 8. Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Large datasets (>1000 rows) slow client-side | High | Virtual scroll (Phase 3) or server-side mode |
| Alpine.js component complexity | Medium | Keep components small, well-documented |
| Browser compatibility | Low | Alpine.js 3.x supports modern browsers |
| Migration effort for existing views | Medium | Gradual adoption, not forced |

---

## 9. Open Questions

1. Should API endpoints be per-controller or centralized?
2. Should cell renderers be Blade components or Alpine.js templates?
3. Should export use backend packages (Laravel Excel) or client-side (SheetJS)?
