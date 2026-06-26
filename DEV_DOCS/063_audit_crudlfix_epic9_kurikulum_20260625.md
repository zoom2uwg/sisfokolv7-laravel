# DEV_DOCS-063: Audit CRUDLFIX Implementation — Epic 9 (Kurikulum Plugin)

- **Tanggal:** 2026-06-25
- **Dibuat oleh:** OpenCode Agent (Anomaly) — model DeepseekV4Flash
- **Proyek:** SISFOKOL v7.00 → Laravel 11 (`sisfokol-laravel/`)
- **Metode:** Perbandingan kode manual vs CRUDLFIX pattern

---

## Ringkasan Eksekutif

**Verdict: ❌ EPIC 9 TIDAK MENGGUNAKAN CRUDLFIX**

| Komponen | CRUDLFIX (21 controllers) | Epic 9 (3 controllers) |
|----------|:-------------------------:|:----------------------:|
| Trait `use Crudlfix` | ✅ 21/21 | ❌ 0/3 |
| Config `crudlfix()` | ✅ 21/21 | ❌ 0/3 |
| `<x-crudlfix.*>` views | ✅ 2 views | ❌ 0/9 views |
| Search/filter | ✅ Otomatis | ❌ Tidak ada |
| Export | ✅ Otomatis | ❌ Tidak ada |
| Data table | ✅ `<x-crudlfix.data-table>` | ❌ Manual HTML |

---

## 1. Apa itu CRUDLFIX?

CRUDLFIX (Create Read Update Delete List Filter Import eXport) adalah reusable trait di `app/Support/Crudlfix/` yang mengeliminasi boilerplate CRUD controller.

**Cara pakai:**
```php
class GuruController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'     => Guru::class,
            'view'      => 'academic.guru',
            'route'     => 'academic.guru',
            'authorize' => 'guru',
            'authType'  => 'policy',
            'search'    => ['nama', 'nip', 'email'],
            'rules'     => [...],
        ];
    }
}
```

**Otomaticole tersedia:**
- `index()` — search, filter, sort, paginate
- `create()` — form view
- `store()` — validate + create + flash message
- `show()` — detail view
- `edit()` — edit form
- `update()` — validate + update + flash message
- `destroy()` — delete + flash message
- `export()` — CSV export

---

## 2. Status CRUDLFIX di Proyek

### 2.1 Controllers yang Menggunakan CRUDLFIX (21)

| # | Controller | Lines | Module |
|---|-----------|------:|--------|
| 1 | `AcademicYearController` | 24 | Admin |
| 2 | `AttendanceTimeController` | 26 | Admin |
| 3 | `SubjectController` | 32 | Admin |
| 4 | `ClassroomController` | 32 | Admin |
| 5 | `ExtracurricularController` | 30 | Admin |
| 6 | `MapelJenisController` | 33 | Academic |
| 7 | `ScheduleController` | 41 | Admin |
| 8 | `KelasSiswaController` | 40 | Academic |
| 9 | `TahunAjaranController` | 43 | Academic |
| 10 | `MapelController` | 44 | Academic |
| 11 | `SemesterController` | 44 | Academic |
| 12 | `OrangTuaController` | 45 | Academic |
| 13 | `KelasController` | 47 | Academic |
| 14 | `SiswaControllerCrudlfix` | 50 | Academic |
| 15 | `GuruController` | 51 | Academic |
| 16 | `ItemPembayaranControllerCrudlfix` | 55 | Finance |
| 17 | `SiswaController` | 59 | Academic |
| 18 | `UserController` | 62 | Admin |
| 19 | `AbsensiController` | 65 | Presence |
| 20 | `TabunganSiswaController` | 100 | Finance |
| 21 | `JadwalController` | 101 | Academic |

### 2.2 Controllers yang TIDAK Menggunakan CRUDLFIX

**Epic 9 — Kurikulum Plugin (3 controllers):**

| Controller | Lines | Selisih vs CRUDLFIX |
|-----------|------:|:--------------------:|
| `KurikulumController` | 86 | +56 lines (186%) |
| `StrukturKurikulumController` | 96 | +46 lines (148%) |
| `KomponenKompetensiController` | 97 | +47 lines (149%) |

**Total:** 279 lines (manual) vs ~110 lines (jika pakai CRUDLFIX)

---

## 3. Analisis Gap per Controller

### 3.1 KurikulumController (86 lines — MANUAL)

**Apa yang ditulis manual:**
```php
// 6× manual authorization
$this->authorize('viewAny', Kurikulum::class);
$this->authorize('create', Kurikulum::class);
$this->authorize('update', $kurikulum);
$this->authorize('delete', $kurikulum);

// 2× manual validation
$validated = $request->validate([...]);
$validated = $request->validate([...]);

// Manual pagination
Kurikulum::orderBy('nama_kurikulum')->paginate(15);

// Hardcoded flash messages
->with('success', 'Kurikulum berhasil ditambahkan.');
->with('success', 'Kurikulum berhasil diperbarui.');
->with('success', 'Kurikulum berhasil dihapus.');
```

**Yang TIDAK ada (fitur CRUDLFIX):**
- ❌ Search (pencarian berdasarkan field tertentu)
- ❌ Filter (filter by status_aktif, jenjang, dll)
- ❌ Sort (multi-column sorting)
- ❌ Export (CSV/Excel)
- ❌ Data table component (bulk actions, responsive)

**Jika pakai CRUDLFIX:**
```php
class KurikulumController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'     => Kurikulum::class,
            'view'      => 'kurikulum::kurikulum',
            'route'     => 'kurikulum',
            'authorize' => 'kurikulum',
            'authType'  => 'policy',
            'search'    => ['kurikulum_id', 'nama_kurikulum'],
            'rules'     => [
                'store' => [
                    'kurikulum_id'   => 'required|string|max:20|unique:kurikulum,kurikulum_id',
                    'nama_kurikulum' => 'required|string|max:100',
                    'status_aktif'   => 'boolean',
                ],
                'update' => [
                    'kurikulum_id'   => 'required|string|max:20|unique:kurikulum,kurikulum_id,{{id}}',
                    'nama_kurikulum' => 'required|string|max:100',
                    'status_aktif'   => 'boolean',
                ],
            ],
        ];
    }
}
```

**Hasil:** 86 → ~30 lines (**65% reduksi**)

### 3.2 StrukturKurikulumController (96 lines — MANUAL)

**Apa yang ditulis manual:**
- Manual authorization × 6
- Manual validation × 2 (dengan enum bug)
- Manual dropdown building (`Kurikulum::where(...)`)
- Hardcoded flash messages

**Yang TIDAK ada:**
- ❌ Cascade-select (kurikulum → struktur)
- ❌ Search/filter
- ❌ Export

**Jika pakai CRUDLFIX:**
```php
class StrukturKurikulumController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'     => StrukturKurikulum::class,
            'view'      => 'kurikulum::struktur',
            'route'     => 'kurikulum.struktur',
            'authorize' => 'kurikulum',
            'authType'  => 'policy',
            'with'      => ['kurikulum'],
            'search'    => ['jenjang', 'kelas', 'fase'],
            'filters'   => [
                'jenjang' => ['column' => 'jenjang', 'operator' => '='],
            ],
            'rules'     => [
                'store' => [
                    'kurikulum_id'   => 'required|exists:kurikulum,id',
                    'jenjang'        => 'required|in:SD,SMP,SMA,SMK',
                    'kelas'          => 'required|string|max:5',
                    'fase'           => 'nullable|in:A,B,C,D,E,F',
                    'jenis_kegiatan' => 'required|in:intrakurikuler,kokurikuler_p5,ekstrakurikuler',
                ],
                // ...
            ],
            'cascades' => [
                'kurikulum_id' => [
                    'query' => fn($value) => StrukturKurikulum::where('kurikulum_id', $value),
                    'value' => 'id',
                    'label' => 'nama',
                ],
            ],
        ];
    }
}
```

**Hasil:** 96 → ~40 lines (**58% reduksi**)

### 3.3 KomponenKompetensiController (97 lines — MANUAL)

**Apa yang ditulis manual:**
- Manual authorization × 6
- Manual validation × 2
- Manual dropdown building × 2 (`StrukturKurikulum::with('kurikulum')->get()->mapWithKeys(...)`)
- Hardcoded flash messages

**Yang TIDAK ada:**
- ❌ Search-select (pencarian struktur)
- ❌ Cascade-select (kurikulum → struktur → komponen)
- ❌ Search/filter
- ❌ Export

**Jika pakai CRUDLFIX:**
```php
class KomponenKompetensiController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'     => KomponenKompetensi::class,
            'view'      => 'kurikulum::komponen',
            'route'     => 'kurikulum.komponen',
            'authorize' => 'kurikulum',
            'authType'  => 'policy',
            'with'      => ['struktur.kurikulum'],
            'search'    => ['kode_kompetensi', 'teks_kompetensi'],
            'rules'     => [
                'store' => [
                    'struktur_id'          => 'required|exists:struktur_kurikulum,id',
                    'kode_kompetensi'      => 'required|string|max:30',
                    'teks_kompetensi'      => 'required|string|max:1000',
                    'pendekatan_pedagogis' => 'nullable|string|max:50',
                ],
                // ...
            ],
            'searchSelects' => [
                'struktur_id' => [
                    'query'  => fn($search) => StrukturKurikulum::with('kurikulum')
                        ->where('kode_kompetensi', 'like', "%{$search}%"),
                    'value'  => 'id',
                    'label'  => fn($item) => "{$item->kurikulum->nama_kurikulum} — {$item->jenjang} Kelas {$item->kelas}",
                ],
            ],
        ];
    }
}
```

**Hasil:** 97 → ~40 lines (**59% reduksi**)

---

## 4. Blade Views — Tidak Menggunakan CRUDLFIX Components

### 4.1 Views yang Menggunakan CRUDLFIX Components (2 files)

| View | Components Used |
|------|----------------|
| `academic/jadwal/index.blade.php` | `<x-crudlfix.data-table>` |
| `academic/jadwal/create.blade.php` | `<x-crudlfix.select>`, `<x-crudlfix.cascade-select>`, `<x-crudlfix.search-select>` |

### 4.2 Epic 9 Views (0 files menggunakan CRUDLFIX)

| View | Lines | Components |
|------|------:|------------|
| `kurikulum/index.blade.php` | 141 | ❌ Manual HTML table |
| `kurikulum/create.blade.php` | 107 | ❌ Manual `<select>` |
| `kurikulum/edit.blade.php` | 90 | ❌ Manual `<select>` |
| `struktur/index.blade.php` | 147 | ❌ Manual HTML table |
| `struktur/create.blade.php` | 116 | ❌ Manual `<select>` |
| `struktur/edit.blade.php` | 96 | ❌ Manual `<select>` |
| `komponen/index.blade.php` | 136 | ❌ Manual HTML table |
| `komponen/create.blade.php` | 94 | ❌ Manual `<select>` |
| `komponen/edit.blade.php` | 85 | ❌ Manual `<select>` |

### 4.3 Perbandingan: Manual vs CRUDLFIX Component

**Manual (Epic 9 — kurikulum/index.blade.php):**
```blade
<table class="w-full text-sm text-left">
  <thead>...</thead>
  <tbody>
    @forelse ($kurikulumList as $kur)
      <tr>
        <td>{{ $kur->kurikulum_id }}</td>
        <td>{{ $kur->nama_kurikulum }}</td>
        ...
        <td>
          <a href="{{ route('kurikulum.edit', $kur) }}">Edit</a>
          <form action="{{ route('kurikulum.destroy', $kur) }}" method="POST">
            @csrf @method('DELETE')
            <button>Delete</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="6">Tidak ada data</td></tr>
    @endforelse
  </tbody>
</table>
{{ $kurikulumList->links() }}
```

**CRUDLFIX Component:**
```blade
<x-crudlfix.data-table
    :data="$kurikulums"
    :columns="[
        'kurikulum_id' => 'Kode',
        'nama_kurikulum' => 'Nama',
        'status_aktif' => 'Status',
    ]"
    :actions="['edit', 'delete']"
    :createRoute="route('kurikulum.create')"
    searchable
    exportable
/>
```

**Hasil:** 141 → ~15 lines (**89% reduksi**)

---

## 5. Dampak

### 5.1 Kode Berlebihan (Boilerplate)

| File | Saat Ini | Jika CRUDLFIX | Selisih |
|------|--------:|-------------:|--------:|
| KurikulumController | 86 | ~30 | **-56 lines** |
| StrukturKurikulumController | 96 | ~40 | **-56 lines** |
| KomponenKompetensiController | 97 | ~40 | **-57 lines** |
| 9 Blade views | 1.012 | ~200 | **-812 lines** |
| **TOTAL** | **1.291** | **~310** | **-981 lines (76%)** |

### 5.2 Fitur yang Hilang

| Fitur | CRUDLFIX | Epic 9 |
|-------|:--------:|:------:|
| Search (pencarian) | ✅ | ❌ |
| Filter (filter by field) | ✅ | ❌ |
| Sort (multi-column) | ✅ | ❌ |
| Export (CSV) | ✅ | ❌ |
| Data table (bulk actions) | ✅ | ❌ |
| Cascade select | ✅ | ❌ |
| Search select (AJAX) | ✅ | ❌ |
| Auto flash message | ✅ | ❌ |
| Pagination | ✅ | ✅ (manual) |
| Authorization | ✅ (otomatis) | ✅ (manual) |

### 5.3 Konsistensi

- 21 controllers sudah pakai CRUDLFIX
- 3 controllers Epic 9 masih manual → **tidak konsisten**
- 2 views sudah pakai `<x-crudlfix.*>` components
- 9 views Epic 9 masih manual HTML → **tidak konsisten**

---

## 6. Rekomendasi

### Prioritas 1: Refaktor Controllers ke CRUDLFIX
```php
// KurikulumController: 86 → ~30 lines
// StrukturKurikulumController: 96 → ~40 lines (fix enum bug juga)
// KomponenKompetensiController: 97 → ~40 lines
```

### Prioritas 2: Refaktor Views ke CRUDLFIX Components
```blade
// index: <x-crudlfix.data-table> (search, sort, export, bulk actions)
// create/edit: <x-crudlfix.select>, <x-crudlfix.cascade-select>
```

### Prioritas 3: Tambahkan API Routes untuk Cascade/Search
```php
Route::get('kurikulum/struktur/api', [StrukturKurikulumController::class, 'api']);
Route::get('kurikulum/komponen/api', [KomponenKompetensiController::class, 'api']);
```

---

## 7. Kesimpulan

Epic 9 (Kurikulum Plugin) **tidak menggunakan CRUDLFIX** — semua 3 controllers dan 9 views ditulis manual. Ini menghasilkan:

1. **981 lines boilerplate** yang bisa dieliminasi (76% reduksi)
2. **8 fitur CRUDLFIX** tidak tersedia (search, filter, sort, export, data-table, cascade-select, search-select, auto flash)
3. **Tidak konsisten** dengan 21 controllers lain yang sudah pakai CRUDLFIX

Refaktor ke CRUDLFIX akan menghasilkan kode yang lebih ringkas, konsisten, dan fitur-rich.

---

*Laporan ini dibuat oleh OpenCode Agent (Anomaly) menggunakan model DeepseekV4Flash.*
