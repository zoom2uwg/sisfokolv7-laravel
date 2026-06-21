# DEV_DOCS-019: Implementation Plan — Epic 4: Plugin System Infrastructure

- **Tanggal:** 2026-06-21 08:28
- **Status:** ⏳ PENDING (Menunggu Persetujuan User)
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** Konversi SISFOKOL v7 (PHP native) → Laravel 11 modular monolith

---

## 🎯 GOAL
Membangun infrastruktur plugin plug-and-play per tenant, meliputi:
1. **PluginContract**: Kontrak interface yang mendefinisikan informasi dasar plugin, ketergantungan, menu navigasi, dan izin yang disumbangkan.
2. **PluginRegistry**: Mekanisme auto-discovery folder `app/Plugins` dan sinkronisasi ke tabel database `plugins` dengan caching status aktif per tenant.
3. **EnsurePluginEnabled**: Middleware penapis route (`plugin:kode_plugin`) dengan bypass SuperAdmin.
4. **PluginActivationService**: Orkestrator pengaktifan dan penonaktifan plugin yang aman, mendaftarkan izin ke database, memicu event `Plugin.Activated`/`Plugin.Deactivated`, dan mencatat audit logs.
5. **Plugins UI**: Dashboard admin premium untuk mengaktifkan dan menonaktifkan plugin.

---

## 🛡️ KEPUTUSAN ARSITEKTUR & KEAMANAN

> [!IMPORTANT]
> **Penyelarasan Framework UI**:
> Seperti halnya Epic 3, tampilan antarmuka daftar plugin (`resources/views/plugins/index.blade.php`) akan dirancang menggunakan **Tailwind CSS** premium dengan grid card modern, efek glassmorphism, dan status badge transparan, bukan menggunakan tabel Bootstrap.

> [!WARNING]
> **Proteksi Perubahan Plugin**:
> Pengaktifan dan penonaktifan plugin diblokir selama mode impersonation aktif (`blockIfImpersonating`) dan akan menghasilkan respon HTTP 403.

---

## 📁 STRUKTUR FILE YANG AKAN DIBUAT/DIUBAH

```
app/
├── Support/
│   ├── PluginContract.php             (Interface kontrak dasar plugin)
│   ├── PluginRegistry.php             (Auto-discovery scan & cache database)
│   └── PluginContext.php              (DI bootstrap plugin)
│
├── Http/Middleware/
│   └── EnsurePluginEnabled.php        (Middleware penyaring route plugin)
│
├── Plugins/Infrastructure/Models/
│   ├── Plugin.php                     (Model metadata plugin)
│   └── TenantPlugin.php               (Model pemetaan tenant-plugin)
│
├── Modules/Auth/
│   ├── Services/
│   │   └── PluginActivationService.php(Service aktivasi plugin & events)
│   ├── Controllers/
│   │   └── PluginController.php       (Controller manajemen UI plugin)
│   ├── Policies/
│   │   └── PluginPolicy.php           (Kebijakan otorisasi aktivasi)
│   └── routes.php                     (Registrasi rute-rute admin/plugins)

bootstrap/
└── app.php                             (Registrasi alias middleware 'plugin')

resources/views/
└── plugins/
    └── index.blade.php                 (View dashboard manajemen plugin)

tests/Feature/Plugin/
├── PluginRegistryTest.php
├── EnsurePluginEnabledTest.php
└── PluginActivationTest.php
```

---

## 📝 DETAIL TAHAPAN EKSEKUSI

### Task 1: Kontrak, Konteks, dan Model
1. Buat interface `PluginContract` berisi 9 method pendefinisian plugin.
2. Buat `PluginContext` untuk mendistribusikan parameter booting (seperti `tenantId` dan event dispatcher).
3. Buat model `Plugin` dan `TenantPlugin` di dalam folder `app/Plugins/Infrastructure/Models/`.

### Task 2: PluginRegistry & Sinkronisasi DB
1. Tulis pengujian fitur `PluginRegistryTest.php` untuk memvalidasi pemindaian manifest disk, sinkronisasi DB, dan pengecekan status aktif.
2. Implementasikan kelas `PluginRegistry` yang menyeken folder `app/Plugins/*`, memuat instance manifest, dan menyinkronkannya ke database.
3. Buat provider `PluginRegistryServiceProvider` untuk meregistrasikan `PluginRegistry` sebagai singleton dan mendaftarkannya di `bootstrap/providers.php`.

### Task 3: EnsurePluginEnabled Middleware
1. Tulis pengujian unit `EnsurePluginEnabledTest.php` untuk memvalidasi pemblokiran rute, kelolosan akses jika aktif, dan bypass SuperAdmin.
2. Buat middleware `EnsurePluginEnabled.php` yang membaca status dari `PluginRegistry`.
3. Daftarkan alias middleware `plugin` di file `bootstrap/app.php`.

### Task 4: PluginActivationService & UI Dashboard
1. Tulis pengujian fitur `PluginActivationTest.php` untuk memvalidasi orkestrasi aktivasi oleh admin tenant, penonaktifan, pemblokiran impersonation, dan audit log.
2. Buat `PluginActivationService` untuk orkestrasi database transaction, pendaftaran izin Spatie secara dinamis, cache flushing, pemicu event (`Plugin.Activated`), dan pencatatan audit log.
3. Buat `PluginController` dengan proteksi `Gate::authorize('plugin.activate')`.
4. Tambahkan rute rilis plugin ke `app/Modules/Auth/routes.php`.
5. Buat view `plugins/index.blade.php` dengan visual card premium Tailwind CSS dan konfirmasi nonaktifkan.

---

## 📈 RENCANA VERIFIKASI

### Pengujian Otomatis (Green Test Target)
```powershell
php83 artisan test tests/Feature/Plugin/PluginRegistryTest.php
php83 artisan test tests/Feature/Plugin/EnsurePluginEnabledTest.php
php83 artisan test tests/Feature/Plugin/PluginActivationTest.php
```

### Verifikasi Manual
1. Pastikan menu "Plugin" ter-render di sidebar jika user memiliki izin `plugin.activate`.
2. Klik tombol "Aktifkan" pada salah satu plugin (seperti Kurikulum). Periksa database pada tabel `tenant_plugins` dan `audit_logs` untuk memastikan pencatatan berhasil.
3. Coba akses rute plugin yang dinonaktifkan dan verifikasi respon HTTP 403.
