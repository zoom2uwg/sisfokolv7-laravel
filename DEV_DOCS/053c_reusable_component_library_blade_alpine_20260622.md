# DEV_DOCS-053c: Reusable Component Library — Blade + Alpine.js

- **Tanggal:** 2026-06-22
- **Status:** 📋 SPECIFICATION — Siap Implementasi
- **Penulis:** ZCode
- **Terhubung ke:** ADR-011, DEV_DOCS-053

---

## ⚡ TUJUAN

Mendefinisikan **reusable Blade components** yang konsisten, modern, dan bisa dipakai di seluruh view SISFOKOL v7. Setiap component:
- Dark theme (slate palette)
- Glassmorphism effect
- Alpine.js untuk interaktivitas
- Responsive (mobile-first)
- Accessible (aria-label, sr-only)

---

## 📁 STRUKTUR FOLDER

```
resources/views/components/
├── ui/                        ← UI Primitives
│   ├── alert.blade.php
│   ├── badge.blade.php
│   ├── button.blade.php
│   ├── card.blade.php
│   ├── modal.blade.php
│   ├── stat-card.blade.php
│   └── empty-state.blade.php
│
├── form/                      ← Form Components
│   ├── input.blade.php
│   ├── select.blade.php
│   ├── textarea.blade.php
│   ├── checkbox.blade.php
│   └── group.blade.php
│
└── table/                     ← Table Components
    ├── wrapper.blade.php
    ├── th.blade.php
    ├── td.blade.php
    └── pagination.blade.php
```

---

## 1. UI PRIMITIVES

### 1.1 `<x-ui.card>` — Glassmorphism Card

**Penggunaan:**
```blade
<x-ui.card title="Daftar Siswa" subtitle="20 siswa aktif">
    {{-- card content --}}
</x-ui.card>

<x-ui.card>
    <x-slot name="header">
        <x-ui.button size="sm">Tambah</x-ui.button>
    </x-slot>
    {{-- card content --}}
    <x-slot name="footer">
        <p>Total: 20</p>
    </x-slot>
</x-ui.card>
```

**Props:**
| Prop | Type | Default | Keterangan |
|------|------|---------|------------|
| `title` | string | null | Card title (opsional) |
| `subtitle` | string | null | Card subtitle (opsional) |
| `padding` | string | 'p-6' | Padding class |
| `class` | string | '' | Extra classes |

**Template:**
```blade
@props(['title' => null, 'subtitle' => null, 'padding' => 'p-6', 'class' => ''])

<div {{ $attributes->merge(['class' => "bg-slate-900/45 backdrop-blur-md border border-slate-700/30 rounded-2xl shadow-lg overflow-hidden {$class}"]) }}>
    @if($title || isset($header))
        <div class="px-6 py-4 border-b border-slate-700/20 flex items-center justify-between">
            @if(isset($header))
                {{ $header }}
            @else
                <div>
                    <h3 class="text-sm font-semibold text-slate-100">{{ $title }}</h3>
                    @if($subtitle)
                        <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-6 py-3 border-t border-slate-700/20 bg-slate-900/30">
            {{ $footer }}
        </div>
    @endif
</div>
```

---

### 1.2 `<x-ui.stat-card>` — Dashboard Stat Card

**Penggunaan:**
```blade
<x-ui.stat-card
    label="Total Siswa"
    value="256"
    icon="fas fa-users"
    color="indigo"
    trend="+12%"
    trend-up="true"
/>
```

**Props:**
| Prop | Type | Default | Keterangan |
|------|------|---------|------------|
| `label` | string | required | Label stat |
| `value` | string | required | Nilai stat |
| `icon` | string | 'fas fa-chart-bar' | Font Awesome icon |
| `color` | string | 'indigo' | Warna: indigo, emerald, amber, rose, teal |
| `trend` | string | null | Trend text (opsional) |
| `trendUp` | bool | true | Trend direction |

**Template:**
```blade
@props(['label', 'value', 'icon' => 'fas fa-chart-bar', 'color' => 'indigo', 'trend' => null, 'trendUp' => true])

@php
    $colorMap = [
        'indigo'  => 'from-indigo-500 to-indigo-600 shadow-indigo-500/20',
        'emerald' => 'from-emerald-500 to-emerald-600 shadow-emerald-500/20',
        'amber'   => 'from-amber-500 to-amber-600 shadow-amber-500/20',
        'rose'    => 'from-rose-500 to-rose-600 shadow-rose-500/20',
        'teal'    => 'from-teal-500 to-teal-600 shadow-teal-500/20',
    ];
    $gradient = $colorMap[$color] ?? $colorMap['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-slate-900/45 backdrop-blur-md border border-slate-700/30 rounded-2xl p-5 flex items-center gap-4 shadow-lg']) }}>
    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $gradient }} shadow-lg text-white text-lg flex-shrink-0">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">{{ $label }}</p>
        <p class="text-2xl font-bold text-slate-100 mt-0.5">{{ $value }}</p>
    </div>
    @if($trend)
        <div class="text-xs font-medium {{ $trendUp ? 'text-emerald-400' : 'text-rose-400' }} flex items-center gap-1">
            <i class="fas fa-arrow-{{ $trendUp ? 'up' : 'down' }} text-[10px]"></i>
            {{ $trend }}
        </div>
    @endif
</div>
```

---

### 1.3 `<x-ui.badge>` — Status Badge

**Penggunaan:**
```blade
<x-ui.badge color="emerald">Aktif</x-ui.badge>
<x-ui.badge color="rose" icon="fas fa-times">Ditolak</x-ui.badge>
<x-ui.badge color="amber" :pulse="true">Pending</x-ui.badge>
```

**Props:**
| Prop | Type | Default | Keterangan |
|------|------|---------|------------|
| `color` | string | 'slate' | Warna: slate, indigo, emerald, amber, rose, teal |
| `icon` | string | null | Icon class (opsional) |
| `pulse` | bool | false | Animasi pulse |

**Template:**
```blade
@props(['color' => 'slate', 'icon' => null, 'pulse' => false])

@php
    $colorMap = [
        'slate'   => 'bg-slate-800 text-slate-300 border-slate-700',
        'indigo'  => 'bg-indigo-950/50 text-indigo-300 border-indigo-800/50',
        'emerald' => 'bg-emerald-950/50 text-emerald-300 border-emerald-800/50',
        'amber'   => 'bg-amber-950/50 text-amber-300 border-amber-800/50',
        'rose'    => 'bg-rose-950/50 text-rose-300 border-rose-800/50',
        'teal'    => 'bg-teal-950/50 text-teal-300 border-teal-800/50',
    ];
    $classes = $colorMap[$color] ?? $colorMap['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {$classes}"]) }}>
    @if($pulse)
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-current"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-current"></span>
        </span>
    @endif
    @if($icon)
        <i class="{{ $icon }} text-[10px]"></i>
    @endif
    {{ $slot }}
</span>
```

---

### 1.4 `<x-ui.button>` — Button Variants

**Penggunaan:**
```blade
<x-ui.button>Simpan</x-ui.button>
<x-ui.button variant="secondary">Batal</x-ui.button>
<x-ui.button variant="danger" icon="fas fa-trash">Hapus</x-ui.button>
<x-ui.button variant="ghost" size="sm" :loading="true">Memproses...</x-ui.button>
<x-ui.button tag="a" href="/siswa/create" variant="primary" icon="fas fa-plus">Tambah Siswa</x-ui.button>
```

**Props:**
| Prop | Type | Default | Keterangan |
|------|------|---------|------------|
| `variant` | string | 'primary' | primary, secondary, danger, ghost, success |
| `size` | string | 'md' | sm, md, lg |
| `icon` | string | null | Icon class (opsional) |
| `tag` | string | 'button' | HTML tag: button, a |
| `loading` | bool | false | Loading state |
| `disabled` | bool | false | Disabled state |

**Template:**
```blade
@props(['variant' => 'primary', 'size' => 'md', 'icon' => null, 'tag' => 'button', 'loading' => false, 'disabled' => false])

@php
    $variantClasses = [
        'primary'   => 'bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white shadow-lg shadow-indigo-500/20',
        'secondary' => 'bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700',
        'danger'    => 'bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white shadow-lg shadow-rose-500/20',
        'success'   => 'bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white shadow-lg shadow-emerald-500/20',
        'ghost'     => 'bg-transparent hover:bg-slate-800 text-slate-300',
    ];
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs rounded-lg gap-1.5',
        'md' => 'px-4 py-2.5 text-sm rounded-xl gap-2',
        'lg' => 'px-6 py-3 text-base rounded-xl gap-2.5',
    ];
    $base = 'inline-flex items-center justify-center font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 active:scale-[0.97] disabled:opacity-50 disabled:cursor-not-allowed';
@endphp

<{{ $tag }} {{ $attributes->merge([
    'class' => "{$base} {$variantClasses[$variant]} {$sizeClasses[$size]}",
    'disabled' => $disabled || $loading,
]) }}>
    @if($loading)
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($icon)
        <i class="{{ $icon }} text-[0.8em]"></i>
    @endif
    {{ $slot }}
</{{ $tag }}>
```

---

### 1.5 `<x-ui.modal>` — Modal Dialog

**Penggunaan:**
```blade
<div x-data="{ open: false }">
    <x-ui.button @click="open = true">Buka Modal</x-ui.button>

    <x-ui.modal x-show="open" @close="open = false" title="Konfirmasi Hapus">
        <p class="text-slate-300">Apakah Anda yakin ingin menghapus data ini?</p>
        <x-slot name="footer">
            <x-ui.button variant="ghost" @click="open = false">Batal</x-ui.button>
            <x-ui.button variant="danger">Hapus</x-ui.button>
        </x-slot>
    </x-ui.modal>
</div>
```

**Props:**
| Prop | Type | Default | Keterangan |
|------|------|---------|------------|
| `title` | string | null | Modal title |
| `maxWidth` | string | 'md' | sm, md, lg, xl |
| `closable` | bool | true | Show close button |

---

### 1.6 `<x-ui.alert>` — Toast Alert

**Penggunaan:**
```blade
<x-ui.alert type="success" :dismissible="true">Data berhasil disimpan!</x-ui.alert>
<x-ui.alert type="error">Terjadi kesalahan.</x-ui.alert>
<x-ui.alert type="warning" icon="fas fa-exclamation-triangle">Perhatian!</x-ui.alert>
```

**Props:**
| Prop | Type | Default | Keterangan |
|------|------|---------|------------|
| `type` | string | 'info' | success, error, warning, info |
| `icon` | string | auto | Icon class (auto-detect by type) |
| `dismissible` | bool | false | Bisa ditutup |

---

### 1.7 `<x-ui.empty-state>` — Empty State

**Penggunaan:**
```blade
<x-ui.empty-state
    icon="fas fa-inbox"
    title="Belum ada data siswa"
    description="Tambahkan siswa baru untuk memulai."
>
    <x-ui.button tag="a" href="/academic/siswa/create" icon="fas fa-plus">
        Tambah Siswa
    </x-ui.button>
</x-ui.empty-state>
```

---

## 2. FORM COMPONENTS

### 2.1 `<x-form.group>` — Form Group (Label + Input + Error)

**Penggunaan:**
```blade
<x-form.group label="Nama Lengkap" name="nama" required>
    <x-form.input name="nama" :value="old('nama', $siswa->nama ?? '')" placeholder="Masukkan nama..." />
</x-form.group>
```

### 2.2 `<x-form.input>` — Text Input

**Penggunaan:**
```blade
<x-form.input name="nama" label="Nama" :value="old('nama')" required />
<x-form.input name="nis" label="NIS" icon="fas fa-id-card" placeholder="2024001" />
<x-form.input name="email" type="email" label="Email" />
```

**Props:**
| Prop | Type | Default | Keterangan |
|------|------|---------|------------|
| `name` | string | required | Field name |
| `type` | string | 'text' | Input type |
| `label` | string | null | Label text |
| `value` | string | '' | Input value |
| `placeholder` | string | '' | Placeholder |
| `icon` | string | null | Left icon |
| `required` | bool | false | Required marker |
| `disabled` | bool | false | Disabled state |

**Template:**
```blade
@props(['name', 'type' => 'text', 'label' => null, 'value' => '', 'placeholder' => '', 'icon' => null, 'required' => false, 'disabled' => false])

@if($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-300 mb-1.5">
        {{ $label }}
        @if($required)
            <span class="text-rose-400">*</span>
        @endif
    </label>
@endif

<div class="relative">
    @if($icon)
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i class="{{ $icon }} text-slate-500 text-sm"></i>
        </div>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => "w-full bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-slate-100 placeholder-slate-500
            focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500/40 transition-all duration-150
            disabled:opacity-50 disabled:cursor-not-allowed
            {$icon ? 'pl-10 pr-4 py-2.5' : 'px-4 py-2.5'}"]) }}
    >
</div>

@error($name)
    <p class="mt-1.5 text-xs text-rose-400 flex items-center gap-1">
        <i class="fas fa-exclamation-circle text-[10px]"></i> {{ $message }}
    </p>
@enderror
```

### 2.3 `<x-form.select>` — Select Dropdown

**Penggunaan:**
```blade
<x-form.select name="kelas_id" label="Kelas" required>
    <option value="">Pilih Kelas...</option>
    @foreach($kelas as $k)
        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
    @endforeach
</x-form.select>
```

### 2.4 `<x-form.textarea>` — Textarea

**Penggunaan:**
```blade
<x-form.textarea name="alamat" label="Alamat" :rows="3" />
```

### 2.5 `<x-form.checkbox>` — Checkbox

**Penggunaan:**
```blade
<x-form.checkbox name="is_active" label="Aktif" :checked="$user->is_active" />
```

---

## 3. TABLE COMPONENTS

### 3.1 `<x-table.wrapper>` — Responsive Table

**Penggunaan:**
```blade
<x-table.wrapper>
    <x-table.th>No</x-table.th>
    <x-table.th>Nama</x-table.th>
    <x-table.th>NIS</x-table.th>
    <x-table.th>Aksi</x-table.th>

    @foreach($siswa as $i => $s)
        <tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors">
            <x-table.td>{{ $siswa->firstItem() + $i }}</x-table.td>
            <x-table.td>{{ $s->nama }}</x-table.td>
            <x-table.td>{{ $s->nis }}</x-table.td>
            <x-table.td>
                <x-ui.button variant="ghost" size="sm" tag="a" href="{{ route('academic.siswa.show', $s) }}">
                    <i class="fas fa-eye"></i>
                </x-ui.button>
            </x-table.td>
        </tr>
    @endforeach
</x-table.wrapper>

{{ $siswa->links() }}
```

### 3.2 `<x-table.th>` — Table Header

```blade
@props(['sortable' => false, 'field' => null])

<th {{ $attributes->merge(['class' => 'px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider']) }}>
    @if($sortable)
        <button class="flex items-center gap-1 hover:text-slate-200 transition">
            {{ $slot }} <i class="fas fa-sort text-[10px] opacity-50"></i>
        </button>
    @else
        {{ $slot }}
    @endif
</th>
```

### 3.3 `<x-table.td>` — Table Cell

```blade
<td {{ $attributes->merge(['class' => 'px-6 py-4 text-sm text-slate-300']) }}>
    {{ $slot }}
</td>
```

---

## 4. PARTIALS (SHARED)

### 4.1 `partials/search-form.blade.php`

**Penggunaan:**
```blade
@include('partials.search-form', [
    'route' => route('academic.siswa.index'),
    'placeholder' => 'Cari siswa berdasarkan nama atau NIS...',
])
```

### 4.2 `partials/delete-confirm.blade.php`

**Penggunaan:**
```blade
@include('partials.delete-confirm', [
    'action' => route('academic.siswa.destroy', $siswa),
    'item' => $siswa->nama,
])
```

### 4.3 `partials/loading-spinner.blade.php`

```blade
<div class="flex items-center justify-center py-12">
    <div class="relative">
        <div class="h-12 w-12 rounded-full border-2 border-slate-700 border-t-indigo-500 animate-spin"></div>
    </div>
</div>
```

---

## 5. MICRO-INTERACTION PATTERNS

### 5.1 Auto-dismiss Toast

```blade
<div x-data="{ show: true }" x-show="show" x-transition
     x-init="setTimeout(() => show = false, 5000)"
     class="...">
    {{ session('success') }}
</div>
```

### 5.2 Confirm Before Delete

```blade
<form action="{{ route('destroy', $item) }}" method="POST"
      x-data="{ confirm: false }"
      @submit.prevent="confirm ? $el.submit() : confirm = true">
    @csrf @method('DELETE')
    <button type="submit" class="..."
            :class="confirm ? 'bg-rose-600' : ''">
        <span x-show="!confirm">Hapus</span>
        <span x-show="confirm">Klik lagi untuk konfirmasi</span>
    </button>
</form>
```

### 5.3 Loading State pada Form Submit

```blade
<form x-data="{ loading: false }" @submit="loading = true">
    <x-ui.button :loading="loading" type="submit">Simpan</x-ui.button>
</form>
```

### 5.4 Dropdown dengan Search

```blade
<div x-data="{ open: false, search: '' }">
    <button @click="open = !open">Pilih...</button>
    <div x-show="open" x-transition @click.away="open = false">
        <input x-model="search" placeholder="Cari..." class="...">
        @foreach($items as $item)
            <div x-show="search === '' || '{{ $item->nama }}'.toLowerCase().includes(search.toLowerCase())">
                {{ $item->nama }}
            </div>
        @endforeach
    </div>
</div>
```

### 5.5 Inline Edit (Click to Edit)

```blade
<div x-data="{ editing: false, value: '{{ $item->nama }}' }">
    <span x-show="!editing" @click="editing = true" class="cursor-pointer hover:text-indigo-400 transition">
        {{ $item->nama }}
        <i class="fas fa-pen text-xs ml-1 opacity-0 group-hover:opacity-100"></i>
    </span>
    <input x-show="editing" x-model="value" @keydown.enter="editing = false" @keydown.escape="editing = false"
           @click.away="editing = false" class="..." autofocus>
</div>
```

---

## 📊 INVENTORY VIEW EXISTING vs PERLU DIBUAT

### Views yang sudah ada (~85 file):
- ✅ `layouts/app.blade.php` — sudah modern
- ✅ `layouts/partials/menu.blade.php` — dynamic sidebar
- ✅ `partials/impersonation_banner.blade.php`
- ✅ `auth/login.blade.php` — glassmorphism
- ✅ `auth/change-password.blade.php`
- ✅ Module views (academic, evaluation, finance, presence, rbac, plugins)

### Components yang perlu dibuat/di-update:
- 🆕 `components/ui/card.blade.php` — ganti `.card` CSS lama
- 🆕 `components/ui/stat-card.blade.php` — ganti `.info-box` CSS lama
- 🆕 `components/ui/badge.blade.php`
- 🆕 `components/ui/button.blade.php`
- 🆕 `components/ui/modal.blade.php`
- 🆕 `components/ui/empty-state.blade.php`
- 🆕 `components/form/input.blade.php`
- 🆕 `components/form/select.blade.php`
- 🆕 `components/form/textarea.blade.php`
- 🆕 `components/form/checkbox.blade.php`
- 🆕 `components/form/group.blade.php`
- 🆕 `components/table/wrapper.blade.php`
- 🆕 `components/table/th.blade.php`
- 🆕 `components/table/td.blade.php`
- 🔄 `components/alert.blade.php` — update ke Tailwind
- 🔄 `components/data-table.blade.php` — update ke Tailwind
- 🔄 `components/info-box.blade.php` — update ke Tailwind

---

## 📎 REFERENSI

- **ADR-011** — UI Architecture decision
- **DEV_DOCS-053** — Master implementation plan
- **layouts/app.blade.php** — Existing modern layout
- **Tailwind CSS** — https://tailwindcss.com/docs
- **Alpine.js** — https://alpinejs.dev/start-here

---

*Dokumen ini adalah spesifikasi teknis untuk implementasi component library.*
*Setiap component bisa diimplementasikan secara independen.*
