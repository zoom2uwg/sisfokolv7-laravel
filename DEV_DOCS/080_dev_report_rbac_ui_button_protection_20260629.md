# Dev Report: RBAC UI Action Button Protection on CRUDLFIX Livewire

**Tanggal:** 29 Juni 2026
**Task:** Enforce strict RBAC on button level (Create, Update, Delete controls visibility)
**Status:** ✅ Selesai & Terverifikasi
**Test:** 158 passed / 401 assertions (full suite green)

---

## 1. Masalah
Meskipun otorisasi AJAX/controller sudah diperketat di backend ( Phase A–E dan perbaikan sebelumnya), antarmuka (UI) Livewire CRUDLFIX masih menampilkan tombol kontrol administratif kepada semua pengguna yang memiliki akses index.

Sebagai contoh:
- Siswa (yang hanya memiliki izin `view` / baca-saja) masih dapat melihat tombol **Edit** (ikon pena biru) dan **Hapus** (ikon sampah merah) di baris tabel Jadwal.
- Siswa juga dapat melihat tombol **Tambah Jadwal** / **Buka Rekening Baru** di bagian atas halaman.
- Hal ini menimbulkan kebingungan UX dan meningkatkan potensi percobaan tampering (meskipun backend akan memblokirnya dengan error 403).

---

## 2. Perubahan Teknis

### A. Helper Otorisasi Boolean di HasCrudlfixAuth Trait
File: `app/Livewire/Crudlfix/Traits/HasCrudlfixAuth.php`

Menambahkan method `checkCrudlfixAction(string $action, ?Model $model = null): bool` yang mengevaluasi apakah pengguna saat ini diperbolehkan melakukan aksi tertentu (`view`, `create`, `update`, `delete`) berdasarkan model otorisasi controller (`policy` atau `permission`). Method ini mengembalikan nilai boolean (`true`/`false`) tanpa memotong eksekusi request (tidak seperti `authorizeCrudlfixAction` yang melempar abort 403).

### B. Proteksi Tombol pada Blade View Komponen
File: `resources/views/livewire/crudlfix/table.blade.php` & `page.blade.php`

- Membungkus tombol **Tambah** (Create) pada orchestrator page dengan:
  ```php
  @if($mode === 'index' && $this->checkCrudlfixAction('create'))
  ```
- Membungkus tombol **Bulk Delete** dengan:
  ```php
  @if(!empty($selected) && $this->checkCrudlfixAction('delete'))
  ```
- Membungkus tombol aksi baris (**Detail, Edit, Hapus**) dengan pengecekan dinamis:
  - Detail: `@if($routePrefix && $showDetail && $this->checkCrudlfixAction('view', $row))`
  - Edit: `@if($showEdit && $this->checkCrudlfixAction('update', $row))`
  - Hapus: `@if($this->checkCrudlfixAction('delete', $row))`

### C. Proteksi Tombol Tambah pada View Traditional (Tier 3)
File: `resources/views/academic/jadwal/index.blade.php` & `resources/views/finance/tabungan/index.blade.php`

Membungkus tombol "Tambah Jadwal" dan "Buka Rekening Baru" pada halaman traditional menggunakan directive Laravel `@can` secara dinamis terhadap kelas model controller:
```php
@can('create', $config->model)
```

---

## 3. Hasil Verifikasi

### A. Automated Tests
- **Command:** `php artisan test`
- **Hasil:** ✅ **158 passed (401 assertions)**. Penambahan filter visual di level Blade aman dan tidak merusak fungsionalitas unit/feature test.

### B. Manual Verification (Browser Test)
Melalui subagent browser:
1. Login sebagai `siswa.2024001` (role student).
2. Membuka halaman Jadwal Pelajaran (`/academic/jadwal`).
3. **Hasil:**
   - Tombol **Tambah Jadwal** di header atas hilang.
   - Tombol **Edit** (pena biru) dan **Hapus** (tong sampah merah) pada seluruh baris tabel hilang sepenuhnya.
   - Siswa disuguhkan dengan tampilan tabel baca-saja (*read-only*) yang bersih dan mematuhi RBAC.
