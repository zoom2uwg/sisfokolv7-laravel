# 044_review_analisis_kritis_deepdive_20260622

- **Tanggal:** 2026-06-22
- **Status:** ANALISIS KRITIS / AUDIT
- **Penulis:** Agentic Assistant (Arena.ai)
- **Topik:** Verifikasi Implementasi vs Dokumentasi (Deep Dive)

---

## 🔴 RINGKASAN EKSEKUTIF: "ILLUSION OF COMPLETENESS"

Temuan utama adalah adanya **"Ilusi Penyelesaian"**. Laporan `DEV_DOCS-039` dan `040` menyatakan status **✅ SELESAI**, namun audit mendalam pada `DEV_DOCS-041`, `042`, dan `043` mengungkapkan kegagalan arsitektural yang fatal. 

Implementasi yang ada saat ini tidak bisa dikatakan "lengkap" karena meskipun kodenya ada, **sistem tidak terintegrasi secara fungsional**.

---

## 🔍 ANALISIS GAP IMPLEMENTASI (SISTEMATIS)

### 1. Divergensi Data: "Parallel Universes" (Kritis/Fatal)
Terdapat dualisme skema database yang tidak sinkron antara modul-modul yang dibangun.
- **Kubu Bahasa Inggris (Core/Legacy):** Menggunakan tabel `students`, `classrooms`, `subjects`, `academic_years` (di `app/Models`). Digunakan oleh modul **Evaluation** dan **Core Finance**.
- **Kubu Bahasa Indonesia (Modular):** Menggunakan tabel `siswa`, `kelas`, `mapel`, `tahun_ajaran` (di `app/Modules/Academic/Models`). Digunakan oleh modul **Academic**, **Presence**, dan **Modular Finance**.

**Bukti Fisik:**
- `app/Models/Student.php` $\leftrightarrow$ `app/Modules/Academic/Models/Siswa.php`
- `app/Models/Classroom.php` $\leftrightarrow$ `app/Modules/Academic/Models/Kelas.php`

**Dampak Nyata:** 
Jika seorang siswa didaftarkan melalui modul Akademik (masuk ke tabel `siswa`), siswa tersebut **tidak akan pernah muncul** di daftar input nilai guru (yang mengambil data dari tabel `students`).

### 2. Gap Integrasi Epic 9 (Kurikulum Plugin) $\rightarrow$ Epic 6 (Evaluation)
Plugin Kurikulum diklaim selesai (`DEV_DOCS-040`), namun secara fungsional ia adalah **"kode mati"**.
- **Klaim:** Plugin menggunakan *Event Subscribers* untuk menyuplai data kompetensi (CP/TP) secara dinamis ke rapor.
- **Fakta:** Audit pada `GradeEntryController.php` menunjukkan **tidak ada** pemanggilan `event()` atau `dispatch()` yang memicu `EvaluationResolveFramework`.
- **Kesimpulan:** Seluruh logika di Plugin Kurikulum tidak pernah dieksekusi oleh sistem utama.

### 3. Gap Implementasi Fisik & File Hilang
- **Missing Controller:** Rute `/curriculum` di `app/Modules/Evaluation/routes.php` merujuk ke `CurriculumController`, namun file tersebut **tidak ada** di disk.
- **Duplicate Finance:** Terdapat dua sistem kasir yang berjalan paralel (`Modular Finance` vs `Core Finance`), berisiko menyebabkan *double bookkeeping*.

### 4. Gap Dokumentasi vs Realitas API-Driven (`DEV_DOCS-041`)
- **SSR Monolith:** Aplikasi 99% adalah Blade-SSR.
- **API Minimalis:** Hanya ada 4 rute API di `api.php`.
- **Missing Infrastructure:** Folder `app/Http/Resources` tidak ada, dan konfigurasi `SANCTUM` di `.env.example` belum lengkap.

### 5. Analisis Epic 10 & 11 (Scaffolding Only)
Laporan `DEV_DOCS-042` mengakui bahwa 8 plugin tambahan dan pipeline ETL hanyalah **scaffolding (boilerplate)**. Statusnya masih `⏳ UNIMPLEMENTED`.

---

## 📜 TINJAUAN SEJARAH GIT
- Terlihat pola pengembangan yang terfragmentasi. Penggabungan (*merge*) dilakukan tanpa sinkronisasi penamaan model.
- Perbaikan pada `RaporGeneratorTest` bersifat "hack" (memaksa ID `Student` = ID `Siswa`) hanya agar tes menjadi hijau, bukan memperbaiki divergensi data.

---

## 🛠️ REKOMENDASI PERBAIKAN & FIX (PRIORITAS)

| Prioritas | Komponen | Tindakan Perbaikan |
| :--- | :--- | :--- |
| **Kritis** | **Data Model** | **Unifikasi Skema.** Hapus salah satu set tabel. Ubah Model di `app/Models` untuk merujuk ke tabel modular (`protected $table = 'siswa'`). |
| **Kritis** | **Event Hook** | **Suntikkan Dispatcher.** Tambahkan `event(new EvaluationResolveFramework(...))` di `GradeEntryController` dan `RaporGeneratorService`. |
| **Tinggi** | **Missing Files** | **Implementasikan `CurriculumController`.** Buat controller yang hilang agar rute kurikulum tidak crash. |
| **Tinggi** | **Finance** | **Konsolidasi Modul Keuangan.** Pilih satu jalur (Modular atau Core) dan hapus redundansinya. |
| **Menengah** | **API Base** | **Build API Foundation.** Buat folder `app/Http/Resources` dan lengkapi config Sanctum. |
| **Menengah** | **Plugin** | **Aktualisasi Epic 10.** Isi logika bisnis pada 8 plugin boilerplate. |

---

**Kesimpulan Akhir:** Implementasi saat ini **BELUM LENGKAP** dan memiliki risiko kegagalan sistem yang tinggi. Laporan `039` dan `040` bersifat superfisial, sedangkan `041-043` adalah cermin realitas yang harus segera ditindaklanjuti.
