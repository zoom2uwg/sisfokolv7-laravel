# DEV_DOCS-038: Detail Tugas Sprint — Epic 9: Plugin Kurikulum (Full Reference Plugin)

- **Tanggal:** 2026-06-21 20:26
- **Status:** ⏳ PENDING (Menunggu Persetujuan/Instruksi Mulai)
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** Konversi SISFOKOL v7 (PHP native) → Laravel 11 modular monolith

---

## 📅 CHECKLIST TUGAS DETAIL SPRINT — EPIC 9: PLUGIN KURIKULUM

### 🏃‍♂️ Epic 9: Plugin Kurikulum (Full Reference Plugin)

- **Task 1: Migrations & Models (3 Kurikulum Tables & Alter Mapel)**
  - [ ] Buat direktori plugin di `app/Plugins/Kurikulum/Database/Migrations`
  - [ ] Buat migrasi `kurikulum` table (master data kurikulum)
  - [ ] Buat migrasi `struktur_kurikulum` table (pemetaan jenjang/kelas/fase)
  - [ ] Buat migrasi `komponen_kompetensi` table (butir kompetensi inti CP/KI/KD)
  - [ ] Buat migrasi `add_mapel_kurikulum_fk` untuk menghubungkan `mapel.kurikulum_id` ke `kurikulum.id`
  - [ ] Jalankan migrasi database (`php83 artisan migrate`)
  - [ ] Buat model `Kurikulum` dengan `BelongsToTenant` & `TracksAuditColumns`
  - [ ] Buat model `StrukturKurikulum` dengan `BelongsToTenant` & `TracksAuditColumns`
  - [ ] Buat model `KomponenKompetensi` dengan `BelongsToTenant` & `TracksAuditColumns`

- **Task 2: Core Events & Dynamic Provider Auto-Registration**
  - [ ] Buat event class `EvaluationResolveFramework` di `app/Modules/Evaluation/Events/EvaluationResolveFramework.php`
  - [ ] Buat event class `RaportRenderSection` di `app/Modules/Evaluation/Events/RaportRenderSection.php`
  - [ ] Buat resolver service `EvaluationFrameworkResolver` di `app/Modules/Evaluation/Services/EvaluationFrameworkResolver.php`
  - [ ] Modifikasi `PluginRegistryServiceProvider` agar secara dinamis meregistrasi service provider dari plugin-plugin yang ditemukan di disk
  - [ ] Tambahkan helper method `jenjang()` di model `Kelas` (`app/Modules/Academic/Models/Kelas.php`)

- **Task 3: Subscribers & Manifest Integration (TDD)**
  - [ ] Buat berkas unit test `tests/Feature/Plugin/KurikulumPluginTest.php`
  - [ ] Buat berkas manifest `app/Plugins/Kurikulum/KurikulumPlugin.php` yang mengimplementasikan `PluginContract`
  - [ ] Buat file `permissions.php` dan `menu.php` di bawah folder plugin untuk memetakan otorisasi & navigasi menu
  - [ ] Buat `EvaluationFrameworkSubscriber` untuk menangani resolusi kompetensi pelajaran
  - [ ] Buat `RaporSectionSubscriber` untuk menginjeksi HTML capaian kompetensi ke rapor
  - [ ] Buat `KurikulumServiceProvider` untuk meregistrasi subscribers dan view namespace `kurikulum::`

- **Task 4: Controllers, Policies, & Views (Tailwind CSS)**
  - [ ] Buat kebijakan otorisasi `KurikulumPolicy` di `app/Plugins/Kurikulum/Policies/KurikulumPolicy.php`
  - [ ] Daftarkan `KurikulumPolicy` pada `AuthServiceProvider`
  - [ ] Buat CRUD Controllers (`KurikulumController`, `StrukturKurikulumController`, `KomponenKompetensiController`)
  - [ ] Daftarkan rute-rute modul kurikulum di `app/Plugins/Kurikulum/routes.php`
  - [ ] Rancang template view premium (Tailwind CSS + glassmorphism) untuk CRUD kurikulum, struktur, dan komponen

- **Task 5: Final Verification**
  - [ ] Jalankan test `KurikulumPluginTest.php` dan pastikan berstatus **PASS**
  - [ ] Eksekusi keseluruhan test suite aplikasi (`php83 artisan test`) untuk memastikan status 100% hijau
  - [ ] Aktifkan plugin via panel admin, verifikasi menu dan fungsi integrasi pengisian kompetensi & rapor di browser
