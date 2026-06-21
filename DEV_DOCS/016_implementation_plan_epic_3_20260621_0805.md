# DEV_DOCS-016: Implementation Plan — Epic 3: RBAC Builder + Field ACL + Menu Renderer

- **Tanggal:** 2026-06-21 08:05
- **Status:** ⏳ PENDING (Menunggu Persetujuan User)
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** Konversi SISFOKOL v7 (PHP native) → Laravel 11 modular monolith

---

## 🎯 GOAL
Membangun sistem kontrol akses granular (RBAC) tingkat lanjut di luar izin dasar `resource.action`, meliputi:
1. **Menu Visibility**: Kontrol visibilitas dinamis item navigasi sidebar berdasarkan per-role override.
2. **Field-level ACL**: Melindungi field database sensitif (`siswa.telepon`, `tabungan.saldo`, dll.) dengan visibilitas `visible`, `readonly`, atau `hidden` melalui direktif Blade khusus.
3. **RBAC Builder UI**: Dashboard admin dengan 4 tab interaktif (Role-Permission Matrix, Menu overrides, Field overrides, User-Role assignment).

---

## 🛡️ KEPUTUSAN ARSITEKTUR & KEAMANAN

> [!IMPORTANT]
> **Konsistensi Visual UI**:
> Meskipun rencana awal menyebutkan Bootstrap 5, layout utama kita `layouts/app.blade.php` menggunakan **Tailwind CSS**. Oleh karena itu, antarmuka RBAC Builder akan dirancang menggunakan Tailwind CSS premium dengan efek glassmorphism agar selaras dengan visual Beranda.

> [!WARNING]
> **Proteksi Impersonation**:
> Seluruh aksi perubahan RBAC (POST/PUT/PATCH/DELETE) wajib memanggil pemeriksaan impersonation (`blockIfImpersonating`) dan akan melempar error 403 jika pengguna sedang dalam sesi impersonasi aktif.

---

## 📁 STRUKTUR FILE YANG AKAN DIBUAT/DIUBAH

```
app/
├── Modules/Auth/
│   ├── Models/
│   │   ├── Menu.php               (Model menus)
│   │   ├── MenuRoleOverride.php   (Model menu_role_overrides)
│   │   ├── Field.php              (Model fields)
│   │   └── FieldRoleOverride.php  (Model field_role_overrides)
│   ├── Controllers/
│   │   ├── RbacRoleController.php (Manajemen Role & Permission)
│   │   ├── RbacMenuController.php (Manajemen Override Menu)
│   │   ├── RbacFieldController.php(Manajemen Override Field)
│   │   └── RbacUserController.php (Assign Role ke User)
│   ├── Services/
│   │   └── RbacBuilderService.php (Service orkestrator penyimpanan RBAC)
│   └── routes.php                 (Registrasi rute-rute admin/rbac)
│
├── Support/
│   ├── FieldAcl.php               (Helper penyelesaian visibilitas field & cache)
│   ├── MenuRenderer.php           (Helper filter menu sidebar & cache)
│   └── BladeDirectives.php        (Registrasi kustom direktif @field dan @fieldAttr)

database/seeders/
├── MenuSeeder.php                 (Menyemai 17 menu default)
├── FieldSeeder.php                (Menyemai 10 field sensitif default)
└── DatabaseSeeder.php             (Registrasi MenuSeeder & FieldSeeder)

resources/views/
├── layouts/partials/
│   └── menu.blade.php             (Ubah sidebar statis menjadi dinamis)
└── rbac/
    ├── index.blade.php            (View Role ↔ Permission Matrix)
    ├── menus.blade.php            (View Menu Visibility Overrides)
    ├── fields.blade.php           (View Field Visibility Overrides)
    └── users.blade.php            (View User Role Assignments)

tests/Feature/Rbac/
├── FieldAclTest.php
├── MenuRendererTest.php
└── RbacBuilderTest.php
```

---

## 📝 DETAIL TAHAPAN EKSEKUSI

### Task 1: Model & Seeders (Database Core)
1. Buat model Eloquent: `Menu`, `MenuRoleOverride`, `Field`, dan `FieldRoleOverride`.
2. Tulis `MenuSeeder` untuk mengisi 17 menu sistem dasar dan `FieldSeeder` untuk menyemai 10 field sensitif.
3. Update `DatabaseSeeder` agar menjalankan seeder baru, lalu jalankan `migrate:fresh --seed` untuk verifikasi.

### Task 2: FieldAcl & Direktif Blade
1. Tulis pengujian unit `FieldAclTest.php` untuk memvalidasi proteksi default, bypass SuperAdmin, dan override per role.
2. Buat helper `App\Support\FieldAcl` dengan optimasi query caching per user/tenant.
3. Buat `App\Support\BladeDirectives` untuk mendaftarkan `@field('kode')` dan `@fieldAttr('kode')` di Blade.
4. Hubungkan ke `AppServiceProvider.php` untuk proses registrasi saat aplikasi boot.

### Task 3: MenuRenderer & Sidebar Dinamis
1. Tulis pengujian unit `MenuRendererTest.php` untuk memvalidasi penyaringan menu per user berdasarkan permission dan override.
2. Buat helper `App\Support\MenuRenderer` dengan caching untuk mempercepat load sidebar.
3. Ubah `layouts/partials/menu.blade.php` agar membaca data dari `MenuRenderer::forUser(auth()->user())`.

### Task 4: UI Builder & Keamanan Aksi
1. Tulis pengujian fitur `RbacBuilderTest.php` untuk memverifikasi larangan akses non-admin dan proteksi perubahan saat impersonation.
2. Buat `RbacBuilderService` untuk menangani modifikasi izin role, menu override, field override, dan user role assignment dengan log audit.
3. Buat 4 controller RBAC dan daftarkan route-nya ke dalam `app/Modules/Auth/routes.php` di bawah prefix `admin/rbac`.
4. Buat 4 view pada folder `resources/views/rbac/` menggunakan Tailwind CSS premium (dengan dark card, glassmorphism, dan status penyimpanan AJAX).

---

## 📈 RENCANA VERIFIKASI

### Pengujian Otomatis (Green Test Target)
```powershell
php83 artisan test tests/Feature/Rbac/FieldAclTest.php
php83 artisan test tests/Feature/Rbac/MenuRendererTest.php
php83 artisan test tests/Feature/Rbac/RbacBuilderTest.php
```

### Verifikasi Manual
1. Login sebagai Admin, ubah permission role `guru` untuk menyembunyikan field `siswa.telepon`.
2. Login sebagai `guru`, pastikan field `siswa.telepon` kosong/tidak ter-render (bukan disembunyikan via CSS, melainkan tidak ada di DOM untuk menangkal inspect element).
3. Uji perubahan RBAC dalam mode impersonation dan pastikan diblokir dengan respon HTTP 403.
