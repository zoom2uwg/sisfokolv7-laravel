# Fix: Academic Mapel — Missing Views

**Tanggal:** 2026-06-25
**Error:** View [academic.mapel.index] not found
**URL:** http://127.0.0.1:8000/academic/mapel
**Status:** ✅ FIXED

---

## Masalah

MapelController menggunakan Crudlfix trait dengan konfigurasi:

```php
'view' => 'academic.mapel',
'route' => 'academic.mapel',
```

Tapi view files tidak ada di direktori `resources/views/academic/mapel/`.

**Error:**
```
View [academic.mapel.index] not found.
InvalidArgumentException
```

---

## Root Cause

- Route `academic.mapel` sudah terdaftar di `app/Modules/Academic/routes.php`
- MapelController sudah dibuat
- Tapi Blade view template belum dibuat

---

## Solusi

### File yang Dibuat

Membuat 4 file view untuk Mapel controller:

1. **`resources/views/academic/mapel/index.blade.php`**
   - Menampilkan daftar mata pelajaran
   - Search & filter
   - Pagination
   - Action buttons (view, edit, delete)

2. **`resources/views/academic/mapel/create.blade.php`**
   - Form tambah mata pelajaran baru
   - Fields: kode, nama, jenis, jenjang, KKM
   - Validation error display

3. **`resources/views/academic/mapel/edit.blade.php`**
   - Form edit mata pelajaran
   - Pre-populated dengan data existing
   - Validation error display

4. **`resources/views/academic/mapel/show.blade.php`**
   - Detail view mata pelajaran
   - Read-only display
   - Edit & Delete buttons

### Template Reuse

Semua view mengikuti pattern yang sama seperti siswa (CRUDLFIX):
- Consistent styling dengan Tailwind CSS
- Dark theme (slate & indigo)
- Authorization checks (@can)
- Form validation display (@error)
- Responsive grid layout

---

## Fitur yang Sudah Ada

### Berdasarkan MapelController Config:

| Fitur | Konfigurasi |
|-------|-----------|
| Search | `'search' => ['kode', 'nama']` |
| Relations | `'with' => ['jenis']` |
| Validation | Store & update rules sudah ada |
| View Data | `'jenisList'` untuk dropdown jenis |

### Validasi di MapelController:

**Store/Create:**
- `kode`: required, string, max 30, unique per tenant
- `nama`: required, string, max 100
- `mapel_jenis_id`: nullable, exists di mapel_jenis
- `kkm`: nullable, numeric, 0-100
- `jenjang`: nullable, string, max 10

**Update:**
- Same rules (kode tidak lagi unique check untuk update)

---

## Testing

Coba akses:
```
GET /academic/mapel → 200 OK (index view)
GET /academic/mapel/create → 200 OK (create view)
GET /academic/mapel/{id} → 200 OK (show view)
GET /academic/mapel/{id}/edit → 200 OK (edit view)
```

---

## Struktur Final

```
resources/views/academic/
├── guru/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── jadwal/
│   └── ...
├── siswa/
│   └── ...
├── mapel/                    ← NEW
│   ├── create.blade.php      ← NEW
│   ├── edit.blade.php        ← NEW
│   ├── index.blade.php       ← NEW
│   └── show.blade.php        ← NEW
└── ...
```

---

## Referensi

| File | Keterangan |
|------|------------|
| `app/Modules/Academic/Controllers/MapelController.php` | CRUDLFIX controller |
| `app/Modules/Academic/Models/Mapel.php` | Mapel model |
| `app/Modules/Academic/routes.php` | Route definition |
| `resources/views/academic/mapel/` | View templates (NEW) |

