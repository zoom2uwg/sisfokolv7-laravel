# DEV_DOCS-037: Rencana Implementasi — Epic 9: Plugin Kurikulum (Full Reference Plugin)

- **Tanggal:** 2026-06-21 20:26
- **Status:** ⏳ PENDING persetujuan
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** Konversi SISFOKOL v7 (PHP native) → Laravel 11 modular monolith

---

## 🛡️ KONTEKS & KEPUTUSAN ARSITEKTUR

### Skema & Relasi Database (3 Tabel Baru + 1 Alter)
1. `kurikulum`: Master data kurikulum per tenant (seperti K13, Kurikulum Merdeka).
2. `struktur_kurikulum`: Pemetaan jenjang (SD/SMP/SMA), kelas, fase, dan jenis kegiatan (intrakurikuler / kokurikuler P5) per kurikulum.
3. `komponen_kompetensi`: Butir kompetensi/materi inti (seperti KI/KD pada K13, CP/TP pada Kurmer) di bawah struktur kurikulum.
4. `mapel` (Alter): Menambahkan foreign key constraint `kurikulum_id` yang mereferensikan `kurikulum.id`.

### Keputusan Desain & Integrasi Event
1. **Pemisahan Modul & Loose Coupling**:
   * Seluruh kode kurikulum diletakkan di `app/Plugins/Kurikulum/` (controllers, models, migrations, views, dan subscribers).
   * Modul evaluasi (`Evaluation`) menembak event `EvaluationResolveFramework` ketika membutuhkan data kompetensi mata pelajaran. Jika plugin Kurikulum aktif, listener-nya akan mengisi framework kompetensi.
   * Cetak rapor mengirim event `RaportRenderSection` sehingga plugin Kurikulum dapat menginjeksi komponen HTML kompetensi siswa ke dalam rapor secara dinamis.
2. **Ketergantungan Tenant Aktif**:
   * Listener hanya akan mengeksekusi logika jika plugin Kurikulum diaktifkan untuk tenant yang sedang mengakses (`TenantContext`).
3. **Pemuatan Provider Otomatis**:
   * Kita akan memperbarui `PluginRegistryServiceProvider` agar secara otomatis mendaftarkan Service Provider dari plugin yang terdaftar/ditemukan pada disk jika kelas provider tersebut ada.

---

## 📁 STRUKTUR FILE YANG AKAN DIBUAT/DIUBAH

```
app/Plugins/Kurikulum/
├── Database/Migrations/
│   ├── 2026_06_20_000500_create_kurikulum_table.php
│   ├── 2026_06_20_000501_create_struktur_kurikulum_table.php
│   ├── 2026_06_20_000502_create_komponen_kompetensi_table.php
│   └── 2026_06_20_000503_add_mapel_kurikulum_fk.php
│
├── Models/
│   ├── Kurikulum.php
│   ├── StrukturKurikulum.php
│   └── KomponenKompetensi.php
│
├── Providers/
│   └── KurikulumServiceProvider.php
│
├── Controllers/
│   ├── KurikulumController.php
│   ├── StrukturKurikulumController.php
│   └── KomponenKompetensiController.php
│
├── Policies/
│   └── KurikulumPolicy.php
│
├── Subscribers/
│   ├── EvaluationFrameworkSubscriber.php
│   └── RaporSectionSubscriber.php
│
├── routes.php
├── permissions.php
├── menu.php
└── KurikulumPlugin.php             (Manifest Plugin)

app/Modules/Evaluation/
├── Events/
│   ├── EvaluationResolveFramework.php
│   └── RaportRenderSection.php
└── Services/
    └── EvaluationFrameworkResolver.php

app/Modules/Academic/Models/
└── Kelas.php                          (tambah helper jenjang())

app/Providers/
├── PluginRegistryServiceProvider.php  (auto-register plugin providers)
└── AuthServiceProvider.php            (daftarkan Kurikulum policy)

resources/views/plugins/kurikulum/     (views layout modern Tailwind)
├── index.blade.php
├── create.blade.php
├── edit.blade.php
├── struktur/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
└── komponen/
    ├── index.blade.php
    ├── create.blade.php
    └── edit.blade.php

tests/Feature/Plugin/
└── KurikulumPluginTest.php            (Feature tests integrasi & aktivasi)
```

---

## 📝 TAHAPAN IMPLEMENTASI

### Task 1: Migrasi Database & Pembuatan Model (3 Tabel + 1 Alter)
* Membuat migrasi `kurikulum`, `struktur_kurikulum`, `komponen_kompetensi` di folder plugin.
* Membuat migrasi alter untuk menambahkan FK `kurikulum_id` pada table `mapel`.
* Menulis model `Kurikulum`, `StrukturKurikulum`, dan `KomponenKompetensi` menggunakan trait `BelongsToTenant` dan `TracksAuditColumns`.
* Jalankan perintah migrasi (`php83 artisan migrate`).

### Task 2: Core Events & Dynamic Provider Auto-Registration
* Membuat event classes `EvaluationResolveFramework` dan `RaportRenderSection` di modul `Evaluation`.
* Membuat service `EvaluationFrameworkResolver` untuk menembak event framework.
* Memodifikasi `PluginRegistryServiceProvider` agar secara dinamis meregistrasikan service provider dari plugin-plugin yang terdeteksi di disk.
* Menambahkan helper `jenjang()` pada model `Kelas` untuk mengambil jenjang branch sekolah.

### Task 3: Subscribers & Manifest Integration (TDD)
* Membuat berkas unit test `tests/Feature/Plugin/KurikulumPluginTest.php`.
* Membuat `KurikulumPlugin` manifest yang mengimplementasikan `PluginContract` beserta definisi menu dan permission.
* Mengimplementasikan `EvaluationFrameworkSubscriber` untuk menangani event resolusi kompetensi pelajaran.
* Mengimplementasikan `RaporSectionSubscriber` untuk menginjeksi komponen kompetensi pada rapor siswa.
* Membuat `KurikulumServiceProvider` untuk mendaftarkan subscribers dan memuat view namespace `kurikulum::`.

### Task 4: Controllers, Policies, & Views (Tailwind CSS)
* Membuat CRUD controllers untuk pengelolaan kurikulum, struktur kurikulum, dan komponen kompetensi.
* Membuat kebijakan otorisasi `KurikulumPolicy` dan mendaftarkannya di `AuthServiceProvider`.
* Merancang halaman view premium (mengikuti tema modern Tailwind + glassmorphism) untuk kurikulum, struktur, dan komponen.

---

## 📈 RENCANA VERIFIKASI

### Pengujian Otomatis (Automated Tests)
```powershell
# Jalankan test spesifik kurikulum plugin
php83 artisan test tests/Feature/Plugin/KurikulumPluginTest.php

# Jalankan keseluruhan test suite aplikasi
php83 artisan test
```

### Verifikasi Manual (Manual Testing)
1. Login sebagai **SuperAdmin** atau **Admin Sekolah** (`admin.sekolah`).
2. Masuk ke halaman **Plugin Management** (`/admin/plugins`), aktifkan plugin **Kurikulum**.
3. Verifikasi item menu **Kurikulum**, **Struktur Kurikulum**, dan **Komponen Kompetensi** muncul di sidebar.
4. Buat kurikulum baru (contoh: *Kurikulum Merdeka*).
5. Buat struktur kurikulum untuk jenjang *SMA*, kelas *10*.
6. Buat komponen kompetensi *CP-001* ("Memahami metode ilmiah") pada struktur tersebut.
7. Edit salah satu mata pelajaran (misal: *Fisika*) dan hubungkan dengan *Kurikulum Merdeka*.
8. Verifikasi pengisian nilai & cetak rapor menampilkan data capaian kompetensi dari plugin.
