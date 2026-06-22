# DEV_DOCS-046: Recovery Plan — Tahap 1: Fondasi Data & Integrasi (Kritis)

- **Tanggal:** 2026-06-22
- **Status:** 🚨 PRIORITAS TERTINGGI (URGENT)
- **Tujuan:** Menghancurkan "Parallel Universes" (Divergensi Model) dan Mengaktifkan "Kode Mati" (Event-Hook Terputus).
- **Terhubung ke:** DEV_DOCS-043, DEV_DOCS-044, ADR-002

---

## ⚡ EXECUTIVE SUMMARY: THE "CURE"

Berdasarkan audit kritis pada `DEV_DOCS-043` dan `DEV_DOCS-044`, aplikasi SISFOKOL v7 mengalami kegagalan integritas data karena adanya dua set tabel untuk entitas yang sama (Kubu Indonesia vs Kubu Inggris). Hal ini menyebabkan fitur-fitur antar modul tidak saling sinkron. 

Dokumen ini adalah **protokol penyelamatan** untuk menyatukan kembali seluruh ekosistem data dan memastikan alur event dinamis antara Core dan Plugin berjalan.

---

## 🛠️ RENCANA AKSI TEKNIS

### 1. Unifikasi Model (Menghancurkan Parallel Universes)
**Masalah:** Dualisme tabel `siswa` vs `students`, `kelas` vs `classrooms`, dll.
**Keputusan:** Menggunakan **Satu Sumber Kebenaran (Single Source of Truth)**. Kita akan menetapkan tabel dari **Kubu Modular (Bahasa Indonesia)** sebagai standar database, namun tetap menggunakan **Nama Class Model (Bahasa Inggris)** untuk konsistensi standar Laravel.

**Action Items:**
- [ ] **Mapping Tabel:** Ubah properti `$table` pada model-model core di `app/Models/` agar merujuk ke tabel modular:
    - `Student` $\rightarrow$ `protected $table = 'siswa';`
    - `Classroom` $\rightarrow$ `protected $table = 'kelas';`
    - `Subject` $\rightarrow$ `protected $table = 'mapel';`
    - `AcademicYear` $\rightarrow$ `protected $table = 'tahun_ajaran';`
- [ ] **Normalisasi Kolom:** Pastikan mapping kolom di Model (misal: `name` $\rightarrow$ `nama`) konsisten melalui accessor/mutator atau perubahan variabel di controller.
- [ ] **Pembersihan Database:** Setelah migrasi kode selesai, hapus tabel-tabel redundan (`students`, `classrooms`, `subjects`, `academic_years`) untuk menghindari kebingungan developer.

### 2. Koneksi Event-Hook (Mengaktifkan Kode Mati)
**Masalah:** Plugin Kurikulum (Epic 9) sudah siap, tetapi Core Evaluation (Epic 6) tidak pernah memanggilnya.
**Action Items:**
- [ ] **Integrasi Grade Entry:** Di `GradeEntryController.php`, suntikkan pemanggilan `EvaluationFrameworkResolver` sebelum merender view form nilai.
    - *Logic:* `event(new EvaluationResolveFramework($subject, $classroom));`
- [ ] **Integrasi Rapor PDF:** Di `RaporGeneratorService.php`, suntikkan pemicuan event `RaportRenderSection` agar data kustom dari plugin (BK, Kurikulum, dll) muncul di PDF.
    - *Logic:* `event(new RaportRenderSection($student, $academicYear, $semester));`
- [ ] **Verifikasi Alur:** Pastikan `EvaluationFrameworkSubscriber` menerima event tersebut dan menyuplai data CP/TP ke framework.

### 3. Fix Missing Controllers (Mencegah Runtime Crash)
**Masalah:** Ada rute yang terdaftar tetapi file controllernya tidak ada di disk.
**Action Items:**
- [ ] **Implementasi `CurriculumController`:** Membuat file `app/Modules/Evaluation/Controllers/CurriculumController.php`.
- [ ] **Implementasi Method:** Minimal menyediakan method `index()` yang merujuk ke view kurikulum agar rute `/curriculum` tidak memicu `Class not found`.

### 4. Konsolidasi Logika Modul Terbelah (Finance & Presence)
**Masalah:** Karena model terbelah, logika bisnis di modul Keuangan dan Presensi juga terbelah menjadi dua versi (Modular vs Core).
**Action Items:**
- [ ] **Unifikasi Kasir:** Menggabungkan `PembayaranController` (Modular) dan `StudentPaymentController` (Core) menjadi satu layanan pembayaran tunggal yang merujuk ke model `Student` (tabel `siswa`).
- [ ] **Sinkronisasi Presensi:** Menghubungkan `QrScannerService` (Modular) dengan `AttendanceController` (Core) sehingga hasil scan QR di gerbang terlihat di daftar absen guru di kelas.
- [ ] **Audit Foreign Key:** Memastikan semua relasi database (FK) menggunakan tabel yang telah diunifikasi.

---

## 🧪 METODE VERIFIKASI (Definition of Done)

Satu tahap dianggap **SELESAI** jika:

1. **Uji Data:** Memasukkan satu siswa di modul Akademik $\rightarrow$ Siswa tersebut **langsung muncul** di modul Nilai (Evaluation) dan modul Kasir (Finance) tanpa perlu input ulang.
2. **Uji Plugin:** Mengubah data CP/TP di Plugin Kurikulum $\rightarrow$ Perubahan tersebut **langsung tercermin** pada kolom penilaian di `GradeEntryController`.
3. **Uji Stabilitas:** Mengakses seluruh rute di `routes/web.php` dan `routes/api.php` tanpa ada error `404` atau `Class not found`.
4. **Uji Keuangan:** Melakukan pembayaran di kasir $\rightarrow$ Tagihan di laporan keuangan core terpotong secara otomatis.

---

## ⚠️ RISIKO & MITIGASI

| Risiko | Mitigasi |
| :--- | :--- |
| **Data Loss** saat penghapusan tabel redundan | Lakukan backup database lengkap sebelum eksekusi dan gunakan migrasi `down()` untuk testing. |
| **Breaking Changes** pada query SQL manual | Lakukan pencarian global (`grep`) untuk semua query `DB::table('students')` dan ubah menjadi `DB::table('siswa')`. |
| **Regresi Test** (Test menjadi merah) | Jalankan `php artisan test` setelah setiap langkah unifikasi model. |
