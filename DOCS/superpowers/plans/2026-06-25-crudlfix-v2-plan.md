# CRUDLFIX v2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Extend CRUDLFIX library with cascading selects, search selects, and full-featured Alpine.js DataTable

**Architecture:** Extend existing Crudlfix trait + CrudlfixConfig with new config keys. Create reusable Alpine.js Blade components for search-select, cascade-select, and data-table. API endpoints handled by trait method.

**Tech Stack:** PHP 8.2, Laravel 11, Alpine.js 3.x (CDN), Tailwind CSS (CDN), Vite

---

## File Structure

```
sisfokol-laravel/
├── app/Support/Crudlfix/
│   ├── Crudlfix.php                    # MODIFY: add api() handler methods
│   ├── CrudlfixConfig.php              # MODIFY: add new config properties
│   └── CrudlfixView.php                # KEEP (legacy compat)
├── resources/views/components/crudlfix/
│   ├── select.blade.php                # CREATE: standard select
│   ├── search-select.blade.php         # CREATE: AJAX search select
│   ├── cascade-select.blade.php        # CREATE: cascading select
│   ├── data-table.blade.php            # CREATE: full-featured DataTable
│   ├── cell-renderers.blade.php        # CREATE: built-in cell renderers
│   └── live-edit.blade.php             # CREATE: inline editing
├── resources/js/crudlfix/
│   ├── data-table.js                   # CREATE: DataTable Alpine component
│   └── live-edit.js                    # CREATE: Live edit Alpine component
└── routes/admin.php                    # MODIFY: add API routes
```

---

## Task 1: Extend CrudlfixConfig with New Properties

**Files:**
- Modify: `sisfokol-laravel/app/Support/Crudlfix/CrudlfixConfig.php`

- [ ] **Step 1: Read current CrudlfixConfig.php**

Read `sisfokol-laravel/app/Support/Crudlfix/CrudlfixConfig.php` to understand current structure.

- [ ] **Step 2: Add new config properties**

Add after line 73 (after `$messages` property):

```php
/** Cascading select definitions */
public ?array $cascades = null;

/** Search select definitions (Select2 replacement) */
public ?array $searchSelects = null;

/** Data table configuration */
public ?array $dataTable = null;

/** Live edit configuration */
public ?array $liveEdit = null;
```

- [ ] **Step 3: Verify no syntax errors**

Run: `php -l sisfokol-laravel/app/Support/Crudlfix/CrudlfixConfig.php`
Expected: No syntax errors

- [ ] **Step 4: Commit**

```bash
git add sisfokol-laravel/app/Support/Crudlfix/CrudlfixConfig.php
git commit -m "feat(crudlfix): add cascade, searchSelect, dataTable config properties"
```

---

## Task 2: Add API Handler Methods to Crudlfix Trait

**Files:**
- Modify: `sisfokol-laravel/app/Support/Crudlfix/Crudlfix.php`

- [ ] **Step 1: Read current Crudlfix.php**

Read `sisfokol-laravel/app/Support/Crudlfix/Crudlfix.php` to understand current structure.

- [ ] **Step 2: Add api() method and helpers**

Add after line 296 (after `validateCrudlfix` method), before closing brace:

```php
// ─── API ENDPOINT HANDLER ──────────────────────────────────────

/**
 * Handle API requests for cascading, search select, and lazy load.
 * Register route: Route::get('api/resource', [Controller::class, 'api']);
 */
public function api(Request $request): \Illuminate\Http\JsonResponse
{
    $type = $request->query('type');

    return match ($type) {
        'cascade' => $this->handleCascade($request),
        'search'  => $this->handleSearchSelect($request),
        default   => response()->json(['error' => 'Unknown type'], 400),
    };
}

/**
 * Handle cascade select: return child options based on parent value.
 */
protected function handleCascade(Request $request): \Illuminate\Http\JsonResponse
{
    $cfg = $this->config();
    $field = $request->query('field');
    $value = $request->query('value');

    $cascade = $cfg->cascades[$field] ?? null;
    if (!$cascade) {
        return response()->json(['error' => 'Cascade not found'], 404);
    }

    $query = ($cascade['query'])($value);
    $results = $query->get()->map(fn ($item) => [
        'value' => $item->{$cascade['value']},
        'label' => $item->{$cascade['label']},
    ]);

    return response()->json($results);
}

/**
 * Handle search select: return filtered options based on query.
 */
protected function handleSearchSelect(Request $request): \Illuminate\Http\JsonResponse
{
    $cfg = $this->config();
    $field = $request->query('field');
    $search = $request->query('q', '');

    $selectConfig = $cfg->searchSelects[$field] ?? null;
    if (!$selectConfig) {
        return response()->json(['error' => 'Search select not found'], 404);
    }

    $query = ($selectConfig['query'])($search);
    $results = $query->limit(20)->get()->map(fn ($item) => [
        'value' => $item->{$selectConfig['value']},
        'label' => $item->{$selectConfig['label']},
    ]);

    return response()->json($results);
}
```

- [ ] **Step 3: Verify no syntax errors**

Run: `php -l sisfokol-laravel/app/Support/Crudlfix/Crudlfix.php`
Expected: No syntax errors

- [ ] **Step 4: Commit**

```bash
git add sisfokol-laravel/app/Support/Crudlfix/Crudlfix.php
git commit -m "feat(crudlfix): add api() handler for cascade and search select"
```

---

## Task 3: Create Standard Select Component

**Files:**
- Create: `sisfokol-laravel/resources/views/components/crudlfix/select.blade.php`

- [ ] **Step 1: Create select.blade.php**

```blade
{{-- components/crudlfix/select.blade.php --}}
@props([
    'name',
    'options'   => [],
    'label'     => 'Pilih...',
    'selected'  => null,
    'required'  => false,
    'disabled'  => false,
    'valueKey'  => 'id',
    'labelKey'  => 'nama',
])

<div>
    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
        {{ $label }}
        @if($required)<span class="text-rose-500">*</span>@endif
    </label>
    <select name="{{ $name }}" id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm']) }}>
        <option value="">-- Pilih --</option>
        @foreach($options as $option)
            @php
                $val = is_array($option) ? $option[$valueKey] : $option->{$valueKey};
                $lbl = is_array($option) ? $option[$labelKey] : $option->{$labelKey};
            @endphp
            <option value="{{ $val }}" {{ old($name, $selected) == $val ? 'selected' : '' }}>
                {{ $lbl }}
            </option>
        @endforeach
    </select>
    @error($name)
        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
    @enderror
</div>
```

- [ ] **Step 2: Verify file created**

Run: `cat sisfokol-laravel/resources/views/components/crudlfix/select.blade.php`
Expected: File content displayed

- [ ] **Step 3: Commit**

```bash
git add sisfokol-laravel/resources/views/components/crudlfix/select.blade.php
git commit -m "feat(crudlfix): add standard select Blade component"
```

---

## Task 4: Create Search Select Component (Select2 Replacement)

**Files:**
- Create: `sisfokol-laravel/resources/views/components/crudlfix/search-select.blade.php`

- [ ] **Step 1: Create search-select.blade.php**

```blade
{{-- components/crudlfix/search-select.blade.php --}}
@props([
    'name',
    'url',
    'field'     => null,
    'label'     => 'Cari...',
    'selected'  => null,
    'selectedLabel' => '',
    'required'  => false,
])

<div x-data="{
    open: false,
    search: '{{ $selectedLabel }}',
    selected: {{ $selected ?? 'null' }},
    options: [],
    loading: false,
    highlighted: -1,

    async fetchOptions() {
        this.loading = true;
        try {
            const res = await fetch('{{ $url }}?type=search&field={{ $field ?? $name }}&q=' + encodeURIComponent(this.search));
            this.options = await res.json();
        } catch (e) {
            this.options = [];
        }
        this.loading = false;
        this.highlighted = -1;
    },

    select(opt) {
        this.selected = opt.value;
        this.search = opt.label;
        this.open = false;
    },

    clear() {
        this.selected = null;
        this.search = '';
        this.options = [];
    },

    handleKeydown(e) {
        if (!this.open) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.highlighted = Math.min(this.highlighted + 1, this.options.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.highlighted = Math.max(this.highlighted - 1, 0);
        } else if (e.key === 'Enter' && this.highlighted >= 0) {
            e.preventDefault();
            this.select(this.options[this.highlighted]);
        } else if (e.key === 'Escape') {
            this.open = false;
        }
    }
}"
@click.away="open = false"
@keydown="handleKeydown($event)"
class="relative">

    <input type="hidden" name="{{ $name }}" :value="selected">

    <div class="relative">
        <input type="text" x-model="search"
            @focus="open = true; fetchOptions()"
            @input.debounce.300ms="fetchOptions()"
            placeholder="{{ $label }}"
            {{ $required ? 'required' : '' }}
            class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm pr-10">

        {{-- Clear button --}}
        <button type="button" x-show="selected !== null" @click="clear()"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>

    {{-- Dropdown --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        class="absolute z-50 mt-1 w-full bg-slate-900 border border-slate-700 rounded-xl shadow-xl max-h-60 overflow-auto">

        <div x-show="loading" class="px-4 py-3 text-center">
            <i class="fas fa-spinner fa-spin text-slate-500"></i>
        </div>

        <template x-for="(opt, idx) in options" :key="opt.value">
            <div @click="select(opt)"
                :class="idx === highlighted ? 'bg-indigo-600/30 text-white' : 'text-slate-300 hover:bg-slate-800'"
                class="px-4 py-2.5 cursor-pointer text-sm transition"
                x-text="opt.label">
            </div>
        </template>

        <div x-show="!loading && options.length === 0 && search.length > 0"
            class="px-4 py-3 text-slate-500 text-sm text-center">
            Tidak ada data ditemukan
        </div>
    </div>

    @error($name)
        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
    @enderror
</div>
```

- [ ] **Step 2: Verify file created**

Run: `cat sisfokol-laravel/resources/views/components/crudlfix/search-select.blade.php`
Expected: File content displayed

- [ ] **Step 3: Commit**

```bash
git add sisfokol-laravel/resources/views/components/crudlfix/search-select.blade.php
git commit -m "feat(crudlfix): add search-select Blade component (Select2 replacement)"
```

---

## Task 5: Create Cascade Select Component

**Files:**
- Create: `sisfokol-laravel/resources/views/components/crudlfix/cascade-select.blade.php`

- [ ] **Step 1: Create cascade-select.blade.php**

```blade
{{-- components/crudlfix/cascade-select.blade.php --}}
@props([
    'name',
    'url',
    'field',
    'dependsOn',
    'label'     => 'Pilih...',
    'selected'  => null,
    'required'  => false,
    'valueKey'  => 'value',
    'labelKey'  => 'label',
])

<div x-data="{
    options: [],
    selected: {{ $selected ?? 'null' }},
    loading: false,

    async loadOptions(parentValue) {
        if (!parentValue) {
            this.options = [];
            this.selected = null;
            return;
        }
        this.loading = true;
        try {
            const res = await fetch('{{ $url }}?type=cascade&field={{ $field }}&value=' + parentValue);
            this.options = await res.json();
        } catch (e) {
            this.options = [];
        }
        this.loading = false;
    }
}"
@cascade-{{ $dependsOn }}.window="loadOptions($event.detail)"
x-init="$nextTick(() => {
    const parentEl = document.querySelector('[name={{ $dependsOn }}]');
    if (parentEl && parentEl.value) loadOptions(parentEl.value);
})">

    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
        {{ $label }}
        @if($required)<span class="text-rose-500">*</span>@endif
    </label>

    <select name="{{ $name }}" id="{{ $name }}" x-model="selected"
        {{ $required ? 'required' : '' }}
        class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm">
        <option value="">-- Pilih --</option>
        <template x-for="opt in options" :key="opt.value">
            <option :value="opt.value" x-text="opt.label"></option>
        </template>
    </select>

    <div x-show="loading" class="mt-1">
        <i class="fas fa-spinner fa-spin text-slate-500 text-xs"></i>
        <span class="text-xs text-slate-500">Memuat...</span>
    </div>

    @error($name)
        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
    @enderror
</div>
```

- [ ] **Step 2: Verify file created**

Run: `cat sisfokol-laravel/resources/views/components/crudlfix/cascade-select.blade.php`
Expected: File content displayed

- [ ] **Step 3: Commit**

```bash
git add sisfokol-laravel/resources/views/components/crudlfix/cascade-select.blade.php
git commit -m "feat(crudlfix): add cascade-select Blade component"
```

---

## Task 6: Create Data Table Component — Simple Variant

**Files:**
- Create: `sisfokol-laravel/resources/views/components/crudlfix/data-table.blade.php`

- [ ] **Step 1: Create data-table.blade.php with simple variant**

```blade
{{-- components/crudlfix/data-table.blade.php --}}
@props([
    'data'              => [],
    'columns'           => [],
    'searchable'        => true,
    'sortable'          => true,
    'paginated'         => true,
    'perPage'           => 15,
    'perPageOptions'    => [10, 25, 50, 100],
    'views'             => ['table', 'list', 'card'],
    'defaultView'       => 'table',
    'selectable'        => false,
    'bulkActions'       => [],
    'exportable'        => false,
    'liveEdit'          => false,
    'liveEditUrl'       => null,
    'nestedRows'        => false,
    'cellRenderers'     => [],
    'rowActions'        => [],
    'variant'           => 'standard',
    'striped'           => true,
    'hoverable'         => true,
    'compact'           => false,
    'emptyMessage'      => 'Tidak ada data',
])

@php
    $isSimple   = $variant === 'simple';
    $isStandard = $variant === 'standard';
    $isAdvanced = $variant === 'advanced';
    $showViewToggle = $isStandard || $isAdvanced;
    $showPerPage    = $isStandard || $isAdvanced;
    $showBulk       = $isAdvanced;
    $showNested     = $isAdvanced && $nestedRows;
    $showLiveEdit   = $isAdvanced && $liveEdit;
@endphp

<div x-data="{
    search: '',
    page: 1,
    perPage: {{ $perPage }},
    sort: null,
    sortDir: 'asc',
    view: '{{ $defaultView }}',
    selected: [],
    selectAll: false,
    expandedRows: [],
    editingCell: null,
    editValue: '',

    get rows() {
        let rows = {{ Js::from($data) }};
        if (this.search) {
            const q = this.search.toLowerCase();
            rows = rows.filter(row =>
                Object.values(row).some(v => String(v).toLowerCase().includes(q))
            );
        }
        if (this.sort) {
            rows.sort((a, b) => {
                const va = this.getNestedValue(a, this.sort);
                const vb = this.getNestedValue(b, this.sort);
                const cmp = String(va).localeCompare(String(vb));
                return this.sortDir === 'asc' ? cmp : -cmp;
            });
        }
        return rows;
    },

    get paginated() {
        if (!{{ $paginated ? 'true' : 'false' }}) return this.rows;
        const start = (this.page - 1) * this.perPage;
        return this.rows.slice(start, start + this.perPage);
    },

    get totalPages() {
        return Math.ceil(this.rows.length / this.perPage);
    },

    getNestedValue(obj, path) {
        return path.split('.').reduce((o, k) => o?.[k], obj) ?? '';
    },

    toggleSort(key) {
        if (this.sort === key) {
            this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.sort = key;
            this.sortDir = 'asc';
        }
    },

    toggleSelectAll() {
        this.selectAll = !this.selectAll;
        this.selected = this.selectAll ? this.paginated.map(r => r.id) : [];
    },

    toggleRow(id) {
        const idx = this.selected.indexOf(id);
        if (idx >= 0) this.selected.splice(idx, 1);
        else this.selected.push(id);
    },

    toggleExpand(id) {
        const idx = this.expandedRows.indexOf(id);
        if (idx >= 0) this.expandedRows.splice(idx, 1);
        else this.expandedRows.push(id);
    },

    isExpanded(id) {
        return this.expandedRows.includes(id);
    },

    startEdit(row, key) {
        this.editingCell = row.id + '-' + key;
        this.editValue = this.getNestedValue(row, key);
    },

    async saveEdit(row, key) {
        const url = '{{ $liveEditUrl }}/' + row.id;
        try {
            await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ [key]: this.editValue }),
            });
            row[key] = this.editValue;
        } catch (e) {
            console.error(e);
        }
        this.editingCell = null;
    },

    cancelEdit() {
        this.editingCell = null;
    },

    exportCSV() {
        const cols = Object.keys({{ Js::from($columns) }});
        const headers = Object.values({{ Js::from($columns) }});
        let csv = headers.join(',') + '\\n';
        this.rows.forEach(row => {
            csv += cols.map(c => '\"' + this.getNestedValue(row, c) + '\"').join(',') + '\\n';
        });
        const blob = new Blob([csv], { type: 'text/csv' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'export.csv';
        a.click();
    }
}">

    {{-- TOOLBAR --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
        {{-- Search --}}
        @if($searchable)
        <div class="relative w-full sm:w-64">
            <input type="text" x-model="search" placeholder="Cari..."
                class="w-full pl-10 pr-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
        </div>
        @endif

        <div class="flex items-center gap-2 flex-wrap">
            {{-- View Toggle --}}
            @if($showViewToggle)
            <div class="flex gap-1 bg-slate-800/50 rounded-xl p-1">
                @foreach($views as $v)
                <button @click="view = '{{ $v }}'"
                    :class="view === '{{ $v }}' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition">
                    @if($v === 'table')<i class="fas fa-table"></i>
                    @elseif($v === 'list')<i class="fas fa-list"></i>
                    @else<i class="fas fa-th-large"></i>@endif
                </button>
                @endforeach
            </div>
            @endif

            {{-- Per Page --}}
            @if($showPerPage)
            <select x-model.number="perPage"
                class="bg-slate-800 border border-slate-700 rounded-lg text-xs text-slate-300 px-2 py-1.5">
                @foreach($perPageOptions as $opt)
                <option value="{{ $opt }}">{{ $opt }}/hal</option>
                @endforeach
            </select>
            @endif

            {{-- Export --}}
            @if($exportable)
            <button @click="exportCSV()"
                class="px-3 py-1.5 bg-emerald-600/20 text-emerald-400 rounded-lg text-xs hover:bg-emerald-600/30">
                <i class="fas fa-download mr-1"></i> Export
            </button>
            @endif

            {{-- Bulk Actions --}}
            @if($showBulk && count($bulkActions) > 0)
            <div x-show="selected.length > 0" class="flex gap-2">
                @foreach($bulkActions as $action)
                <button @click="$dispatch('bulk-{{ $action }}', selected)"
                    class="px-3 py-1.5 bg-rose-600/20 text-rose-400 rounded-lg text-xs hover:bg-rose-600/30">
                    {{ ucfirst($action) }} (<span x-text="selected.length"></span>)
                </button>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- TABLE VIEW --}}
    <div x-show="view === 'table'" class="overflow-x-auto rounded-xl border border-slate-800">
        <table class="w-full text-sm text-left text-slate-300">
            <thead class="text-xs text-slate-400 uppercase bg-slate-800/50 sticky top-0 z-10">
                <tr>
                    @if($showBulk)
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" @change="toggleSelectAll()"
                            class="rounded bg-slate-800 border-slate-700 text-indigo-500 focus:ring-indigo-500">
                    </th>
                    @endif
                    @if($showNested)
                    <th class="px-4 py-3 w-10"></th>
                    @endif
                    @foreach($columns as $key => $label)
                    <th @click="{{ $sortable ? "toggleSort('$key')" : '' }}"
                        class="px-4 py-3 {{ $sortable ? 'cursor-pointer hover:text-white' : '' }} {{ $compact ? 'py-2' : '' }}">
                        <div class="flex items-center gap-1">
                            {{ $label }}
                            @if($sortable)
                            <span x-show="sort === '{{ $key }}'" class="text-indigo-400">
                                <i :class="sortDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fas text-xs"></i>
                            </span>
                            @endif
                        </div>
                    </th>
                    @endforeach
                    @if(count($rowActions) > 0)
                    <th class="px-4 py-3">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <template x-for="row in paginated" :key="row.id">
                    <tr class="border-b border-slate-800/50 {{ $hoverable ? 'hover:bg-slate-800/30' : '' }} {{ $striped ? 'even:bg-slate-900/30' : '' }}">
                        @if($showBulk)
                        <td class="px-4 py-3">
                            <input type="checkbox" :value="row.id" x-model="selected"
                                class="rounded bg-slate-800 border-slate-700 text-indigo-500 focus:ring-indigo-500">
                        </td>
                        @endif
                        @if($showNested)
                        <td class="px-4 py-3">
                            <button @click="toggleExpand(row.id)" class="text-slate-400 hover:text-white">
                                <i :class="isExpanded(row.id) ? 'fa-chevron-down' : 'fa-chevron-right'" class="fas text-xs"></i>
                            </button>
                        </td>
                        @endif
                        @foreach($columns as $key => $label)
                        <td class="px-4 py-3 {{ $compact ? 'py-2' : '' }}">
                            @if($showLiveEdit)
                            <div @click="startEdit(row, '{{ $key }}')" class="cursor-pointer hover:bg-slate-700/50 rounded px-1 -mx-1">
                                <template x-if="editingCell === row.id + '-{{ $key }}'">
                                    <input type="text" x-model="editValue"
                                        @keydown.enter="saveEdit(row, '{{ $key }}')"
                                        @keydown.escape="cancelEdit()"
                                        @blur="saveEdit(row, '{{ $key }}')"
                                        class="w-full px-2 py-1 bg-slate-800 border border-indigo-500 rounded text-sm text-slate-200 focus:outline-none"
                                        x-ref="editInput">
                                </template>
                                <template x-if="editingCell !== row.id + '-{{ $key }}'">
                                    <span x-text="getNestedValue(row, '{{ $key }}')"></span>
                                </template>
                            </div>
                            @else
                            <span x-text="getNestedValue(row, '{{ $key }}')"></span>
                            @endif
                        </td>
                        @endforeach
                        @if(count($rowActions) > 0)
                        <td class="px-4 py-3">
                            {{ $slot }}
                        </td>
                        @endif
                    </tr>
                </template>

                {{-- Empty state --}}
                <template x-if="paginated.length === 0">
                    <tr>
                        <td colspan="{{ count($columns) + ($showBulk ? 1 : 0) + ($showNested ? 1 : 0) + (count($rowActions) > 0 ? 1 : 0) }}"
                            class="px-4 py-12 text-center text-slate-500">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- LIST VIEW --}}
    <div x-show="view === 'list'" class="space-y-2">
        <template x-for="row in paginated" :key="row.id">
            <div class="p-4 bg-slate-800/30 rounded-xl border border-slate-800 hover:border-slate-700 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if($showBulk)
                        <input type="checkbox" :value="row.id" x-model="selected"
                            class="rounded bg-slate-800 border-slate-700 text-indigo-500">
                        @endif
                        <div>
                            @foreach($columns as $key => $label)
                            <div class="flex gap-2">
                                <span class="text-xs text-slate-500 w-24">{{ $label }}:</span>
                                <span class="text-sm text-slate-300" x-text="getNestedValue(row, '{{ $key }}')"></span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @if(count($rowActions) > 0)
                    <div>{{ $slot }}</div>
                    @endif
                </div>
            </div>
        </template>
    </div>

    {{-- CARD VIEW --}}
    <div x-show="view === 'card'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="row in paginated" :key="row.id">
            <div class="p-4 bg-slate-800/30 rounded-xl border border-slate-800 backdrop-blur-md hover:border-slate-700 transition">
                @foreach($columns as $key => $label)
                <div class="mb-3">
                    <span class="text-xs text-slate-500 uppercase tracking-wider">{{ $label }}</span>
                    <p class="text-sm text-slate-200 mt-0.5" x-text="getNestedValue(row, '{{ $key }}')"></p>
                </div>
                @endforeach
                @if(count($rowActions) > 0)
                <div class="mt-3 pt-3 border-t border-slate-700/50">{{ $slot }}</div>
                @endif
            </div>
        </template>
    </div>

    {{-- NESTED ROW CONTENT --}}
    @if($showNested)
    <template x-for="row in paginated" :key="'nested-' + row.id">
        <div x-show="isExpanded(row.id)" x-transition
            class="p-4 bg-slate-900/50 border border-slate-800 rounded-xl mt-2">
            {{ $nested ?? '' }}
        </div>
    </template>
    @endif

    {{-- PAGINATION --}}
    @if($paginated)
    <div class="flex flex-col sm:flex-row justify-between items-center mt-4 gap-3">
        <span class="text-sm text-slate-400"
            x-text="'Menampilkan ' + Math.min((page-1)*perPage+1, rows.length) + '-' + Math.min(page*perPage, rows.length) + ' dari ' + rows.length + ' data'">
        </span>
        <div class="flex gap-1">
            <button @click="page = Math.max(1, page-1)" :disabled="page === 1"
                class="px-3 py-1.5 bg-slate-800 rounded-lg text-sm text-slate-300 disabled:opacity-30 hover:bg-slate-700 transition">
                <i class="fas fa-chevron-left text-xs"></i>
            </button>
            <template x-for="p in totalPages" :key="p">
                <button @click="page = p"
                    :class="page === p ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                    class="px-3 py-1.5 rounded-lg text-sm transition"
                    x-text="p"
                    x-show="p === 1 || p === totalPages || Math.abs(p - page) <= 2">
                </button>
            </template>
            <button @click="page = Math.min(totalPages, page+1)" :disabled="page === totalPages"
                class="px-3 py-1.5 bg-slate-800 rounded-lg text-sm text-slate-300 disabled:opacity-30 hover:bg-slate-700 transition">
                <i class="fas fa-chevron-right text-xs"></i>
            </button>
        </div>
    </div>
    @endif

</div>
```

- [ ] **Step 2: Verify file created**

Run: `cat sisfokol-laravel/resources/views/components/crudlfix/data-table.blade.php | head -20`
Expected: First 20 lines displayed

- [ ] **Step 3: Commit**

```bash
git add sisfokol-laravel/resources/views/components/crudlfix/data-table.blade.php
git commit -m "feat(crudlfix): add full-featured data-table Blade component with 3 variants"
```

---

## Task 7: Add API Route for JadwalController (Example)

**Files:**
- Modify: `sisfokol-laravel/app/Modules/Academic/routes.php`

- [ ] **Step 1: Read current routes.php**

Read `sisfokol-laravel/app/Modules/Academic/routes.php` to find existing route group.

- [ ] **Step 2: Add API route**

Add inside the appropriate route group:

```php
// CRUDLFIX API endpoints
Route::get('api/jadwal', [JadwalController::class, 'api'])->name('academic.jadwal.api');
```

- [ ] **Step 3: Verify route registered**

Run: `cd sisfokol-laravel && php artisan route:list --name=academic.jadwal.api`
Expected: Route displayed

- [ ] **Step 4: Commit**

```bash
git add sisfokol-laravel/app/Modules/Academic/routes.php
git commit -m "feat(academic): add API route for JadwalController cascading"
```

---

## Task 8: Update JadwalController with New Config

**Files:**
- Modify: `sisfokol-laravel/app/Modules/Academic/Controllers/JadwalController.php`

- [ ] **Step 1: Read current JadwalController.php**

Read `sisfokol-laravel/app/Modules/Academic/Controllers/JadwalController.php`.

- [ ] **Step 2: Add cascade and searchSelect config**

Update the `crudlfix()` method to add new config keys:

```php
protected function crudlfix(): array
{
    return [
        'model'      => Jadwal::class,
        'view'       => 'academic.jadwal',
        'route'      => 'academic.jadwal',
        'authorize'  => 'jadwal',
        'search'     => [],
        'with'       => ['tahunAjaran', 'semester', 'kelas', 'mapel', 'guru'],

        // NEW: Cascading — tahun ajaran → semester
        'cascades'   => [
            'tahun_ajaran_id' => [
                'target' => 'semester_id',
                'query'  => fn ($value) => \App\Modules\Academic\Models\Semester::where('tahun_ajaran_id', $value),
                'value'  => 'id',
                'label'  => 'nama',
            ],
        ],

        // NEW: Search select for guru
        'searchSelects' => [
            'guru_id' => [
                'query' => fn ($q) => \App\Modules\Academic\Models\Guru::where('nama', 'like', "%{$q}%")->where('aktif', true),
                'value' => 'id',
                'label' => 'nama',
            ],
        ],

        'rules' => [
            'store' => [
                'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
                'semester_id'     => 'required|exists:semester,id',
                'kelas_id'        => 'required|exists:kelas,id',
                'mapel_id'        => 'required|exists:mapel,id',
                'guru_id'         => 'required|exists:guru,id',
                'hari'            => 'required|integer|min:1|max:7',
                'jam_ke'          => 'required|integer|min:1|max:10',
                'jam_mulai'       => 'required|date_format:H:i',
                'jam_selesai'     => 'required|date_format:H:i|after:jam_mulai',
                'ruang'           => 'nullable|string|max:30',
            ],
        ],
        'viewData' => [
            'tahunAjarans' => \App\Modules\Academic\Models\TahunAjaran::orderBy('nama', 'desc')->get(),
            'kelasList'    => \App\Modules\Academic\Models\Kelas::orderBy('tingkat')->orderBy('nama')->get(),
            'mapels'       => \App\Modules\Academic\Models\Mapel::orderBy('nama')->get(),
            // guru REMOVED from viewData — now uses searchSelect
        ],
    ];
}
```

- [ ] **Step 3: Verify no syntax errors**

Run: `php -l sisfokol-laravel/app/Modules/Academic/Controllers/JadwalController.php`
Expected: No syntax errors

- [ ] **Step 4: Commit**

```bash
git add sisfokol-laravel/app/Modules/Academic/Controllers/JadwalController.php
git commit -m "feat(academic): update JadwalController with cascade and searchSelect config"
```

---

## Task 9: Update Jadwal Blade Views to Use New Components

**Files:**
- Modify: `sisfokol-laravel/resources/views/academic/jadwal/create.blade.php`
- Modify: `sisfokol-laravel/resources/views/academic/jadwal/index.blade.php`

- [ ] **Step 1: Read current create.blade.php**

Read `sisfokol-laravel/resources/views/academic/jadwal/create.blade.php` (if exists, otherwise create).

- [ ] **Step 2: Update create.blade.php with new components**

```blade
@extends('layouts.app')

@section('title', 'Tambah Jadwal')
@section('page-title', 'Tambah Jadwal Pelajaran')

@section('content')
<div class="card">
    <form action="{{ route('academic.jadwal.store') }}" method="POST">
        @csrf
        <div class="card-body space-y-4">
            {{-- Cascading: Tahun Ajaran → Semester --}}
            <x-crudlfix.select
                name="tahun_ajaran_id"
                :options="$tahunAjarans"
                label="Tahun Ajaran"
                :required="true"
                x-on:change="$dispatch('cascade-tahun_ajaran_id', $event.target.value)" />

            <x-crudlfix.cascade-select
                name="semester_id"
                dependsOn="tahun_ajaran_id"
                url="{{ route('academic.jadwal.api') }}"
                field="tahun_ajaran_id"
                label="Semester"
                :required="true" />

            {{-- Standard selects --}}
            <x-crudlfix.select name="kelas_id" :options="$kelasList" label="Kelas" :required="true" />
            <x-crudlfix.select name="mapel_id" :options="$mapels" label="Mapel" :required="true" />

            {{-- Search select for guru --}}
            <x-crudlfix.search-select
                name="guru_id"
                url="{{ route('academic.jadwal.api') }}"
                field="guru_id"
                label="Cari guru..."
                :required="true" />

            {{-- Other fields --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Hari</label>
                    <select name="hari" required class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">-- Pilih --</option>
                        @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'] as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Jam Ke</label>
                    <input type="number" name="jam_ke" min="1" max="10" required
                        class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Jam Mulai</label>
                    <input type="time" name="jam_mulai" required
                        class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Jam Selesai</label>
                    <input type="time" name="jam_selesai" required
                        class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ruang</label>
                <input type="text" name="ruang" maxlength="30"
                    class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection
```

- [ ] **Step 3: Update index.blade.php with data-table**

```blade
@extends('layouts.app')

@section('title', 'Jadwal')
@section('page-title', 'Data Jadwal Pelajaran')

@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3 class="card-title">Daftar Jadwal</h3>
        <a href="{{ route('academic.jadwal.create') }}" class="btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Tambah
        </a>
    </div>
    <div class="card-body">
        <x-crudlfix.data-table
            :data="$jadwals->map(fn($j) => [
                'id'             => $j->id,
                'tahunAjaran'    => $j->tahunAjaran?->nama ?? '-',
                'semester'       => $j->semester?->nama ?? '-',
                'kelas'          => $j->kelas?->nama ?? '-',
                'mapel'          => $j->mapel?->nama ?? '-',
                'guru'           => $j->guru?->nama ?? '-',
                'hari'           => ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'][$j->hari - 1] ?? '-',
                'jam'            => $j->jam_mulai . ' - ' . $j->jam_selesai,
            ])"
            :columns="[
                'tahunAjaran' => 'Tahun Ajaran',
                'semester'    => 'Semester',
                'kelas'       => 'Kelas',
                'mapel'       => 'Mapel',
                'guru'        => 'Guru',
                'hari'        => 'Hari',
                'jam'         => 'Jam',
            ]"
            variant="advanced"
            :selectable="true"
            :bulkActions="['delete']"
            :exportable="true"
            :rowActions="['edit', 'delete']">

            <div class="flex gap-2">
                <a :href="'{{ route('academic.jadwal.index') }}/' + row.id + '/edit'"
                    class="text-indigo-400 hover:text-indigo-300 transition">
                    <i class="fas fa-edit"></i>
                </a>
                <form :action="'{{ route('academic.jadwal.index') }}/' + row.id" method="POST" class="inline"
                    onsubmit="return confirm('Yakin hapus?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-rose-400 hover:text-rose-300 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>

        </x-crudlfix.data-table>
    </div>
</div>
@endsection
```

- [ ] **Step 4: Verify no Blade errors**

Run: `cd sisfokol-laravel && php artisan view:clear && php artisan view:cache`
Expected: No errors

- [ ] **Step 5: Commit**

```bash
git add sisfokol-laravel/resources/views/academic/jadwal/
git commit -m "feat(academic): update Jadwal views with cascade, search-select, and data-table components"
```

---

## Task 10: Manual Testing

- [ ] **Step 1: Start dev server**

Run: `cd sisfokol-laravel && php artisan serve`
Expected: Server running on http://127.0.0.1:8000

- [ ] **Step 2: Test cascade select**

1. Open jadwal create form
2. Select a tahun ajaran
3. Verify semester dropdown auto-loads

- [ ] **Step 3: Test search select**

1. Open jadwal create form
2. Type in guru search field
3. Verify results appear after typing

- [ ] **Step 4: Test data table**

1. Open jadwal index page
2. Test search, sort, pagination
3. Test view toggle (table/list/card)
4. Test bulk select
5. Test export CSV

- [ ] **Step 5: Test responsive**

1. Resize browser to tablet width (768px)
2. Verify table scrollable
3. Resize to mobile width (375px)
4. Verify card/list view

- [ ] **Step 6: Commit final state**

```bash
git add -A
git commit -m "feat(crudlfix): CRUDLFIX v2 complete — cascade, search-select, data-table"
```

---

## Summary

| Task | Description | LOC (est.) |
|------|-------------|------------|
| 1 | Extend CrudlfixConfig | +15 |
| 2 | Add API handler to Crudlfix trait | +60 |
| 3 | Standard select component | +35 |
| 4 | Search select component | +90 |
| 5 | Cascade select component | +60 |
| 6 | Data table component | +300 |
| 7 | API route for Jadwal | +5 |
| 8 | Update JadwalController config | +20 |
| 9 | Update Jadwal blade views | +100 |
| 10 | Manual testing | - |
| **Total** | | **~685 LOC** |
