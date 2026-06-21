# DEV_DOCS-018: Walkthrough — Epic 3: RBAC Builder + Field ACL + Menu Renderer

- **Tanggal:** 2026-06-21 08:31
- **Status:** ✅ SELESAI (100% Green Tests)
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** Konversi SISFOKOL v7 (PHP native) → Laravel 11 modular monolith

---

## 🛠️ Perubahan yang Diimplementasikan

### 1. Struktur Database & Model Core
- Menggunakan tabel-tabel migrasi yang sudah tersedia (`menus`, `menu_role_overrides`, `fields`, dan `field_role_overrides`).
- Membuat model Eloquent:
  - [Menu](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Auth/Models/Menu.php)
  - [MenuRoleOverride](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Auth/Models/MenuRoleOverride.php) (menggunakan trait `BelongsToTenant`)
  - [Field](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Auth/Models/Field.php)
  - [FieldRoleOverride](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Auth/Models/FieldRoleOverride.php) (menggunakan trait `BelongsToTenant`)
- Membuat seeder database:
  - [MenuSeeder](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/database/seeders/MenuSeeder.php) (menyemai 17 menu default dengan ikon Font Awesome premium).
  - [FieldSeeder](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/database/seeders/FieldSeeder.php) (menyemai 10 field sensitif default dengan kategori masing-masing).
  - Mendaftarkannya di [DatabaseSeeder](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/database/seeders/DatabaseSeeder.php).

### 2. Resolusi Keamanan FieldAcl & Direktif Blade
- Membuat kelas helper [FieldAcl](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Support/FieldAcl.php) untuk menyelesaikan visibilitas field (`visible`, `readonly`, `hidden`) dengan orkestrasi caching dan pembatasan team context Spatie.
- Membuat direktif Blade kustom pada [BladeDirectives](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Support/BladeDirectives.php):
  - `@field('siswa.telepon') ... @endfield`: Mencegah rendering input/data di server jika visibilitas bernilai `hidden`.
  - `@fieldAttr('siswa.telepon')`: Menyuntikkan atribut `disabled` secara otomatis jika visibilitas bernilai `readonly`.
- Mendaftarkannya pada [AppServiceProvider](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Providers/AppServiceProvider.php).

### 3. Resolusi Menu Dinamis (MenuRenderer) & Sidebar
- Membuat kelas helper [MenuRenderer](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Support/MenuRenderer.php) untuk memfilter visibilitas menu berdasarkan izin (`permission_required`) dan override menu-role khusus tenant.
- Mengubah sidebar menu hardcoded pada [menu.blade.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/resources/views/layouts/partials/menu.blade.php) menjadi sidebar dinamis berbasis Tailwind CSS dengan deteksi rute aktif dan pengecekan keamanan `Route::has()` untuk mencegah error jika modul rute belum diimplementasikan.

### 4. RBAC Builder Service & 4-Tab UI
- Membuat service orkestrator [RbacBuilderService](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Auth/Services/RbacBuilderService.php) yang mengelola perubahan RBAC dengan keamanan impersonation guard (`blockIfImpersonating`), pembersihan cache otomatis, dan pencatatan audit trails.
- Membuat 4 Controller:
  - [RbacRoleController](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Auth/Controllers/RbacRoleController.php) (Role ↔ Permission Matrix)
  - [RbacMenuController](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Auth/Controllers/RbacMenuController.php) (Menu Visibility Overrides)
  - [RbacFieldController](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Auth/Controllers/RbacFieldController.php) (Field Visibility Overrides)
  - [RbacUserController](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Auth/Controllers/RbacUserController.php) (User ↔ Role Assignment)
- Menghindari konflik dengan resource `admin/users` bawaan dengan mendaftarkan prefix rute baru `/admin/user-roles` di [routes.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Auth/routes.php).
- Membuat 4 View premium dengan Tailwind CSS di folder `resources/views/rbac/`:
  - `index.blade.php` (Matriks izin AJAX real-time)
  - `menus.blade.php` (Pengelolaan override menu)
  - `fields.blade.php` (Pengelolaan override field database)
  - `users.blade.php` (Manajemen penetapan role pengguna dengan modal Alpine.js)

---

## 📈 Hasil Verifikasi Pengujian

### 1. Pengujian Otomatis (Green Status)
Seluruh 51 pengujian di dalam aplikasi berjalan sukses:
- **FieldAclTest**: Memvalidasi filter sensitivitas field default, bypass SuperAdmin, dan override role (`teacher`).
- **MenuRendererTest**: Memvalidasi tampilan menu dasar, filter izin peran (`student`), dan override visibilitas menu.
- **RbacBuilderTest**: Memvalidasi pencegahan akses bagi non-admin, fungsionalitas update matriks izin oleh admin, dan proteksi pemblokiran penulisan RBAC selama mode impersonation aktif.

### 2. Log Eksekusi Test Suite
```powershell
  Tests:    51 passed (93 assertions)
  Duration: 65.35s
```
Semua test suite hijau dan tidak ada regresi pada pengujian autentikasi (Epic 2) maupun fondasi (Epic 1) sebelumnya.
