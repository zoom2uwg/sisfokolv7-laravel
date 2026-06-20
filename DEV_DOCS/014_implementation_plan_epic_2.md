# DEV_DOCS-014: Implementation Plan — Epic 2: Auth Module

- **Tanggal:** 2026-06-20 23:45
- **Status:** ⏳ PENDING (Menunggu Persetujuan User)
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** Konversi SISFOKOL v7 (PHP native) → Laravel 11 modular monolith

---

## 🎯 GOAL
Membangun modul autentikasi lengkap pada folder `app/Modules/Auth/`. Termasuk alur login/logout dengan throttling, enkripsi password menggunakan bcrypt, regenerasi session keamanan, middleware `ForcePasswordReset` untuk user pasca-ETL, sistem impersonation hierarkis yang aman (dengan banner peringatan dan proteksi aksi sensitif), dashboard cerdas sesuai role, dan Audit Log viewer untuk melacak aktivitas sistem.

---

## 🛡️ KEPUTUSAN KEAMANAN & SPESIFIKASI

> [!IMPORTANT]
> **Impersonation Safety Constraints**:
> - Seluruh aksi penulisan data (POST/PUT/PATCH/DELETE) ke rute-rute sensitif (seperti pengelolaan user, RBAC, password, dan aktivasi plugin) akan **diblokir** saat mode impersonation aktif.
> - Fitur impersonation diaktifkan/dinonaktifkan secara global melalui opsi `.env` `IMPERSONATION_ENABLED`.
> - Banner peringatan merah bertuliskan *"Anda sedang login sebagai [Nama User]..."* beserta tombol keluar cepat (*"Kembali ke akun saya"*) akan di-inject ke layout utama saat mendeteksi sesi impersonasi aktif.

> [!NOTE]
> **Pendekatan Estetika Antarmuka**:
> - Meskipun layout admin utama berbasis **AdminLTE 3** (Bootstrap 4), halaman mandiri (Login dan Ganti Password Wajib) akan dirancang menggunakan **Bootstrap 5** dengan gradien warna modern, bayangan kartu lembut (*card shadow*), efek *glassmorphism*, mikro-animasi pada form login, serta Google Fonts (*Inter/Plus Jakarta Sans*).
> - Di dalam layout AdminLTE, tampilan Audit Log dan dashboard akan dipercantik dengan badge status dan shadow card agar serasi dengan visual modern.

---

## 📁 STRUKTUR FILE YANG AKAN DIBUAT/DIUBAH

```
app/Modules/Auth/
├── Controllers/
│   ├── AuthController.php          (Login, logout, session management)
│   ├── DashboardController.php     (Role-aware landing dashboard)
│   ├── ImpersonationController.php (Mengatur start/stop impersonation)
│   ├── PasswordResetController.php (Form & proses reset password wajib)
│   └── AuditLogController.php      (List & filter log aktivitas)
├── Models/
│   └── AuditLog.php               (Model immutable untuk audit_logs)
├── Requests/
│   ├── LoginRequest.php            (Validasi username & password)
│   ├── ChangePasswordRequest.php   (Validasi ubah password & kekuatan password)
│   └── StartImpersonationRequest.php
├── Services/
│   ├── ImpersonationService.php   (Business logic impersonasi)
│   └── AuditLogger.php            (Helper logging global)
├── Observers/
│   └── UserObserver.php            (Auto-log perubahan database model User)
├── Policies/
│   └── AuditLogPolicy.php          (Akses data terisolasi per tenant)
└── routes.php                      (Definisi rute sub-modul Auth)

app/Http/Middleware/
├── ForcePasswordReset.php          (Redirect user dengan flag must_reset_password)
└── BlockWhileImpersonating.php     (Memblokir request POST/PUT/DELETE sensitif)

app/Providers/
├── EventServiceProvider.php        (Registrasi observer model)
└── AuthServiceProvider.php         (Registrasi policy)

bootstrap/
└── app.php                         (Registrasi middleware & alias)

resources/views/
├── auth/
│   ├── login.blade.php             (UI halaman login premium)
│   └── change-password.blade.php   (UI reset password premium)
├── dashboard/
│   └── index.blade.php             (UI dashboard default)
├── audit/
│   └── index.blade.php             (UI log audit dengan filter)
├── errors/
│   └── impersonation-blocked.blade.php (Halaman error 403 khusus)
└── partials/
    └── impersonation_banner.blade.php (Banner floating warning)

tests/
└── Feature/Auth/
    ├── LoginTest.php
    ├── ForcePasswordResetTest.php
    ├── ImpersonationTest.php
    └── DashboardTest.php
└── Unit/Auth/
    └── AuditLoggerTest.php
```

---

## 📝 DETAIL TAHAPAN EKSEKUSI

### Task 1: Alur Login Utama
1. **Tulis pengujian** `tests/Feature/Auth/LoginTest.php` untuk login, pembatasan rate-limiting, redirect user aktif, status nonaktif, dan logging aktivitas.
2. Buat file `LoginRequest.php` untuk validasi input.
3. Buat `AuthController.php` dengan fitur `Auth::attempt`, regenerasi session, dan pencatatan waktu login (`last_login_at`).
4. Buat halaman login premium pada `resources/views/auth/login.blade.php`.
5. Daftarkan rute login dengan throttling (`throttle:5,1`) di `app/Modules/Auth/routes.php`.

### Task 2: Layanan AuditLogger & Observers
1. Buat model `AuditLog.php` yang mendukung pembersihan data otomatis setelah 2 tahun (`MassPrunable`).
2. Tulis pengujian unit `tests/Unit/Auth/AuditLoggerTest.php`.
3. Buat service singleton `AuditLogger.php` untuk merekam detail event, user, tenant, IP address, dan browser agent.
4. Buat `UserObserver.php` untuk merekam event modifikasi profile dan password otomatis.
5. Registrasikan observer di `EventServiceProvider.php`.

### Task 3: Middleware Reset Password Wajib
1. Tulis pengujian fitur `tests/Feature/Auth/ForcePasswordResetTest.php`.
2. Buat middleware `ForcePasswordReset.php` yang mengalihkan user dengan flag `must_reset_password = true` ke halaman reset (kecuali route pengecualian).
3. Buat request `ChangePasswordRequest.php` untuk validasi kecocokan password lama dan kekuatan password baru.
4. Buat `PasswordResetController.php` beserta view `resources/views/auth/change-password.blade.php`.
5. Daftarkan rute dan daftarkan middleware di `bootstrap/app.php`.

### Task 4: Impersonation & Safety Guard
1. Tulis pengujian impersonasi komprehensif `tests/Feature/Auth/ImpersonationTest.php`.
2. Implementasikan `ImpersonationService.php` dengan pembatasan hak akses (SuperAdmin bebas impersonasi, Admin Sekolah hanya dalam satu tenant).
3. Buat controller `ImpersonationController.php` dan daftarkan route start/stop.
4. Buat middleware `BlockWhileImpersonating.php` yang mendeteksi request POST/PUT/DELETE pada rute-rute manajemen RBAC/Password/Plugin/User untuk melempar error 403.
5. Injeksi template banner warning `resources/views/partials/impersonation_banner.blade.php` ke dalam template utama `layouts/adminlte.blade.php`.

### Task 5: Dashboard & Viewer Audit Log
1. Tulis pengujian visual dashboard `tests/Feature/Auth/DashboardTest.php`.
2. Integrasikan `DashboardController.php` untuk menampilkan pesan sambutan dinamis.
3. Buat policy `AuditLogPolicy.php` untuk membatasi visibilitas log audit (Admin sekolah hanya melihat log tenant mereka, SuperAdmin melihat semuanya).
4. Buat `AuditLogController.php` beserta tampilan pencarian data `resources/views/audit/index.blade.php` lengkap dengan pagination.

---

## 📊 RENCANA VERIFIKASI

### Pengujian Otomatis
Jalankan test suite menggunakan php83:
```bash
php83 artisan test tests/Feature/Auth/LoginTest.php
php83 artisan test tests/Unit/Auth/AuditLoggerTest.php
php83 artisan test tests/Feature/Auth/ForcePasswordResetTest.php
php83 artisan test tests/Feature/Auth/ImpersonationTest.php
php83 artisan test tests/Feature/Auth/DashboardTest.php
```

### Verifikasi Manual
1. Pastikan tampilan Login dan Ganti Password tampil premium.
2. Cek database `audit_logs` setelah aktivitas login/logout terjadi.
3. Coba lakukan impersonasi user lain dan pastikan aksi mutasi data ke route sensitif diblokir.
