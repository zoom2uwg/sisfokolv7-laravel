# Fix: Academic Kelas — Missing View Files (Error 500)

**Tanggal:** 2026-06-26  
**Investigator:** AI Agent  
**Status:** ✅ Resolved  
**Referensi terkait:** `066_fix_academic_mapel_missing_views_20260625.md`

---

## 📋 Deskripsi Masalah

### Gejala
Saat mengakses halaman `http://127.0.0.1:8000/academic/kelas`, browser menampilkan error **HTTP 500** dengan pesan:

```
InvalidArgumentException: View [academic.kelas.index] not found.
  at vendor/laravel/framework/src/Illuminate/View/FileViewFinder.php:139
```

### Dampak
- Halaman manajemen kelas tidak dapat diakses sama sekali
- Controller dan routing sudah benar, hanya view yang hilang
- Fitur CRUD kelas (tambah, edit, hapus) tidak bisa digunakan

---

## 🔍 Root Cause Analysis

### Investigasi
1. **Browser test** dilakukan pada `http://127.0.0.1:8000/academic/kelas`
2. Error 500 menunjukkan `View [academic.kelas.index] not found`
3. Dicek struktur `resources/views/academic/`:

```
resources/views/academic/
├── guru/
│   └── index.blade.php       ✅ Ada
├── jadwal/
│   ├── create.blade.php      ✅ Ada
│   └── index.blade.php       ✅ Ada
├── mapel/
│   ├── create.blade.php      ✅ Ada
│   ├── edit.blade.php        ✅ Ada
│   ├── index.blade.php       ✅ Ada
│   └── show.blade.php        ✅ Ada
├── siswa/
│   ├── create.blade.php      ✅ Ada
│   ├── edit.blade.php        ✅ Ada
│   ├── index.blade.php       ✅ Ada
│   └── show.blade.php        ✅ Ada
└── kelas/                    ❌ TIDAK ADA (folder belum dibuat)
```

### Root Cause
Folder `resources/views/academic/kelas/` **belum pernah dibuat**. Controller sudah ada dan berfungsi, tetapi view files tidak pernah dibuat bersamaan dengan controller.

Pola yang sama pernah terjadi sebelumnya pada `academic.mapel` (lihat DEV_DOCS 066).

---

## 🛠️ Analisis Teknis

### KelasController

Controller menggunakan trait `Crudlfix` dengan konfigurasi:

```php
protected function crudlfix(): array
{
    return [
        'model'      => Kelas::class,
        'view'       => 'academic.kelas',       // → resources/views/academic/kelas/
        'route'      => 'academic.kelas',
        'authorize'  => 'kelas',
        'search'     => ['nama'],
        'with'       => ['waliKelas', 'branch'],
        'viewData' => [
            'gurus'    => Guru::where('aktif', true)->orderBy('nama')->get(),
            'branches' => Branch::orderBy('nama')->get(),
        ],
        'perPage' => 20,
    ];
}
```

### Variabel yang Dikirim ke View

| Variabel | Sumber | Keterangan |
|----------|--------|------------|
| `$kelas` | `Str::camel(Str::plural('Kelas'))` | Paginator berisi daftar kelas |
| `$search` | `$request->input('search')` | String pencarian |
| `$config` | `CrudlfixConfig` instance | Konfigurasi CRUD |
| `$gurus` | `viewData['gurus']` | Daftar guru aktif (untuk dropdown) |
| `$branches` | `viewData['branches']` | Daftar cabang (untuk dropdown) |

> **Catatan Penting:** `Str::plural('Kelas')` menghasilkan `kelas` (bukan `kelass`).
> Selalu verifikasi dengan:
> ```bash
> php83 artisan tinker --execute="echo Str::camel(Str::plural('NamaModel'));"
> ```

---

## ✅ Solusi yang Diterapkan

### File yang Dibuat

#### 1. `resources/views/academic/kelas/index.blade.php`

View utama daftar kelas dengan fitur:
- **Search bar** — pencarian berdasarkan nama kelas
- **Tabel data** — kolom: Nama Kelas, Tingkat, Wali Kelas, Kapasitas, Cabang, Aksi
- **Badge tingkat** — menampilkan level kelas dengan styling indigo
- **Tombol Edit** — hanya muncul jika `@can('kelas.update')`
- **Tombol Hapus** — dengan konfirmasi, hanya jika `@can('kelas.delete')`
- **Pagination** — menggunakan `$kelas->links()`
- **Flash success** — pesan sukses dari operasi CRUD
- **Empty state** — pesan jika belum ada data

#### 2. `resources/views/academic/kelas/create.blade.php`

Form tambah kelas dengan field:
- **Nama Kelas** (required, text) — contoh: VII-A, X IPA 1
- **Tingkat** (required, number, min:1 max:12)
- **Kapasitas** (optional, number, min:1 max:100)
- **Wali Kelas** (optional, dropdown dari `$gurus`)
- **Cabang** (optional, dropdown dari `$branches`)

#### 3. `resources/views/academic/kelas/edit.blade.php`

Form edit kelas dengan perbedaan dari create:
- Nilai field diisi dari `$kelas` (model yang diedit) dengan fallback `old()`
- Method spoofing `@method('PUT')`
- Action ke `route('academic.kelas.update', $kelas)`

### Perintah yang Dijalankan

```bash
# Buat folder view
New-Item -ItemType Directory -Path "resources\views\academic\kelas" -Force

# Clear compiled view cache setelah membuat file baru
php83 artisan view:clear
```

---

## 🧪 Hasil Verifikasi

| Item | Sebelum | Sesudah |
|------|---------|---------|
| HTTP Status | 500 | ✅ 200 |
| Halaman index | ❌ Error view not found | ✅ Tampil normal |
| Tabel data | ❌ Tidak ada | ✅ Data kelas terload |
| Form create | ❌ Error | ✅ Form siap pakai |
| Form edit | ❌ Error | ✅ Form pre-filled |
| Pagination | ❌ Tidak ada | ✅ Berfungsi |
| Search | ❌ Tidak ada | ✅ Berfungsi |

---

## 📌 Catatan & Pelajaran

### Pola Bug Berulang
Ini adalah kasus yang **sama** dengan DEV_DOCS 065 dan DEV_DOCS 066.
Controller dibuat tanpa view-nya — terjadi berulang kali.

**Rekomendasi:** Tambahkan checklist di workflow pembuatan modul baru.

### Checklist View untuk Modul Crudlfix Baru

- [ ] `resources/views/{prefix}/{resource}/index.blade.php`
- [ ] `resources/views/{prefix}/{resource}/create.blade.php`
- [ ] `resources/views/{prefix}/{resource}/edit.blade.php`
- [ ] (Opsional) `resources/views/{prefix}/{resource}/show.blade.php`

### Gotcha: Nama Variabel Paginator di View
`Str::plural()` pada kata Bahasa Indonesia bisa tidak terduga:
- `Str::plural('Kelas')` → `kelas` (tidak berubah)
- `Str::plural('Siswa')` → `siswas`
- `Str::plural('Guru')` → `gurus`

Jika nama variabel paginator sama dengan nama variabel loop, gunakan alias berbeda di `@forelse`:
```blade
{{-- $kelas = paginator, $k = item dalam loop --}}
@forelse($kelas as $k)
    {{ $k->nama }}
@endforelse
```

---

## 📁 Ringkasan File

| File | Operasi |
|------|---------|
| `resources/views/academic/kelas/index.blade.php` | ✅ BARU DIBUAT |
| `resources/views/academic/kelas/create.blade.php` | ✅ BARU DIBUAT |
| `resources/views/academic/kelas/edit.blade.php` | ✅ BARU DIBUAT |

**File yang TIDAK diubah** (sudah benar):
- `app/Modules/Academic/Controllers/KelasController.php`
- `app/Modules/Academic/Models/Kelas.php`
- Routing (sudah terdaftar sebelumnya)
