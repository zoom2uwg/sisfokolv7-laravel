# DEV_DOCS-026: Walkthrough — Epic 5: Academic Module (Akademik Sekolah)

- **Tanggal:** 2026-06-21 09:15
- **Status:** ✅ SELESAI & TERVERIFIKASI
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** Konversi SISFOKOL v7 (PHP native) → Laravel 11 modular monolith

---

## 🛠️ PERUBAHAN YANG DIIMPLEMENTASIKAN

### 1. Migrasi Database (11 Tabel Akademik)
- Seluruh 11 tabel akademik telah berhasil dimigrasikan ke MySQL di bawah folder [app/Modules/Academic/Database/Migrations/](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Academic/Database/Migrations/):
  - `mapel_jenis` (tipe mapel: Wajib, Mulok, Peminatan)
  - `tahun_ajaran` (tahun ajaran aktif/nonaktif)
  - `semester` (semester ganjil/genap per tahun ajaran)
  - `orang_tua` (data wali murid)
  - `siswa` (data identitas siswa)
  - `siswa_orang_tua` (relasi pivot siswa & orang tua)
  - `guru` (data guru pendidik)
  - `kelas` (data rombongan belajar dan wali kelas)
  - `kelas_siswa` (sejarah penempatan kelas siswa per tahun ajaran)
  - `mapel` (daftar mata pelajaran & KKM)
  - `jadwal` (jadwal pelajaran dengan constraint unik guru/kelas per slot)

### 2. Model & Model Factories (Tenant Isolation & Audit Trail)
- 11 model akademik didefinisikan dengan menggunakan trait `BelongsToTenant` dan `TracksAuditColumns` untuk menjamin isolasi data per sekolah secara dinamis dan pencatatan riwayat audit otomatis.
- Mengatasi pencarian factory Laravel di modular monolith dengan mendefinisikan static method `newFactory()` di model `Siswa`, `Guru`, `Kelas`, `TahunAjaran`, dan `Mapel` agar mengarah ke `\Database\Factories\<Model>Factory` secara eksplisit.

### 3. Mesin Validasi Bentrok Jadwal (`JadwalConflictChecker`)
- Service [JadwalConflictChecker.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Academic/Services/JadwalConflictChecker.php) memvalidasi tabrakan jadwal mengajar guru atau ruang/kelas pada hari & jam ke yang sama sebelum data dimasukkan ke database.
- Terverifikasi lewat unit test [JadwalConflictTest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/tests/Feature/Academic/JadwalConflictTest.php).

### 4. Logika Kenaikan Kelas Berkelanjutan (`KelasSiswaPromotionService`)
- Service [KelasSiswaPromotionService.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Academic/Services/KelasSiswaPromotionService.php) menyalin siswa dari kelas tapel lama ke kelas tapel baru secara transaksional, idempotent (bebas duplikasi), dan menjaga riwayat kelas masa lalu di tabel `kelas_siswa` agar tidak tertimpa.
- Terverifikasi lewat unit test [KelasSiswaPromotionTest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/tests/Feature/Academic/KelasSiswaPromotionTest.php).

### 5. Siswa CRUD & Field ACL Blade Views
- **Controller**: [SiswaController.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Academic/Controllers/SiswaController.php) menggunakan `Gate::authorize()` untuk otorisasi aksi.
- **Form Requests**: `StoreSiswaRequest` & `UpdateSiswaRequest` untuk validasi NIS unik per tenant.
- **Policy**: `SiswaPolicy` mengamankan data agar hanya bisa diakses oleh tenant pemilik.
- **Observer**: `SiswaObserver` merekam aksi `created`, `updated`, `deleted` ke audit log otomatis.
- **Blade Views**: 4 halaman premium dengan tema gelap (dark theme) dan grid layout Tailwind CSS tersemat di `resources/views/academic/siswa/` (`index`, `create`, `edit`, `show`). Visibilitas nomor telepon dikendalikan oleh direktif Blade kustom `@field('siswa.telepon')` dan `@fieldAttr('siswa.telepon')` (Field ACL).
- Terverifikasi lewat unit test [SiswaCrudTest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/tests/Feature/Academic/SiswaCrudTest.php).

### 6. Kebijakan Keamanan Tambahan
- Membuat [GuruPolicy.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Academic/Policies/GuruPolicy.php), [KelasPolicy.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Academic/Policies/KelasPolicy.php), dan [JadwalPolicy.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Academic/Policies/JadwalPolicy.php), lalu mendaftarkannya di [AppServiceProvider.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Providers/AppServiceProvider.php).
- Terverifikasi lewat unit test [TenantIsolationTest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/tests/Feature/Academic/TenantIsolationTest.php).

---

## 📈 HASIL VERIFIKASI PENGUJIAN

Seluruh test suite untuk modul akademik berjalan sukses 100% hijau:

```powershell
# JadwalConflictTest
PASS  Tests\Feature\Academic\JadwalConflictTest
✓ no conflict for new jadwal
✓ conflict same kelas same slot
✓ conflict same guru same slot

# KelasSiswaPromotionTest
PASS  Tests\Feature\Academic\KelasSiswaPromotionTest
✓ promote moves siswa to next kelas in new tapel
✓ promote idempotent does not duplicate

# SiswaCrudTest
PASS  Tests\Feature\Academic\SiswaCrudTest
✓ authorized admin can view siswa index
✓ teacher can view siswa index but cannot create
✓ admin can create siswa
✓ admin can update siswa
✓ admin can delete siswa
✓ tenant isolation on siswa

# TenantIsolationTest
PASS  Tests\Feature\Academic\TenantIsolationTest
✓ all academic models enforce tenant isolation
```

Semua pengujian lolos dalam waktu singkat dan fungsionalitas multi-tenant bekerja secara sempurna di tingkat isolasi database query.
