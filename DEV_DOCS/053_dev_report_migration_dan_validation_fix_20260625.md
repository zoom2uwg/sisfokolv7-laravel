# Dev Report: Database Migration, Seeding, and Validation Fixes
**Tanggal:** 2026-06-25  
**Oleh:** AI-Agent (Antigravity)

---

## 1. Ringkasan Masalah & Solusi

### A. Duplikasi Primary Key pada Seeding (`DemoSeeder.php`)
- **Masalah:** Pasca konsolidasi tabel ("Parallel Universes" database divergence) di mana entitas bahasa Inggris (`classrooms`, `students`) dan Indonesia (`kelas`, `siswa`) disatukan di bawah tabel modular Indonesia (`kelas`, `siswa`), `DemoSeeder` mencoba melakukan `create` berturut-turut pada model Legacy dan model Modular untuk ID yang sama. Hal ini menyebabkan error: `SQLSTATE[23000]: Duplicate entry '1' for key 'PRIMARY'`.
- **Solusi:** Mengubah logika `DemoSeeder` agar setelah model pertama di-create (yang secara otomatis menyinkronkan data lewat Event Observers/Boot saving ke tabel yang sama), model pasangannya diambil menggunakan `find($id)` lalu di-`update` alih-alih di-`create` baru.

### B. Validasi Mengarah ke Tabel Non-Eksisten (`classrooms` & `students`)
- **Masalah:** Karena tabel `classrooms` dan `students` sudah dihapus/dikosongkan dan seluruh entitas diarahkan ke `kelas` dan `siswa`, seluruh request validation rule yang menggunakan `exists:classrooms`, `unique:classrooms`, `exists:students`, dll. mengalami kegagalan query (`Table 'sisfokol_laravel.classrooms' doesn't exist`).
- **Solusi:** Memperbarui semua validasi di Laravel Request Form dan Controller agar mengarah ke tabel yang aktif (`kelas` untuk classrooms, `siswa` untuk students).

### C. Kegagalan Unique/Duplicate Key pada Test Suite (`GradeCalculatorTest` & `RaporGeneratorTest`)
- **Masalah:** Saat menjalankan unit/feature test, test suite mencoba membuat data setup dengan memanggil `TahunAjaran::create` dan `AcademicYear::create` (atau `Siswa::create` dan `Student::save`) secara terpisah. Karena tabel fisik mereka sudah disatukan di bawah `tahun_ajaran` dan `siswa`, tindakan ini melanggar primary/unique key constraint (`Duplicate entry`).
- **Solusi:** Menyesuaikan data setup di test suite (`GradeCalculatorTest` dan `RaporGeneratorTest`) agar mengambil data menggunakan `find()` dari database dan meng-`update` alih-alih membuat entri baru.

---

## 2. Berkas yang Dimodifikasi

Berikut adalah daftar berkas yang telah diperbarui beserta tautannya:

### Seeder & Database
- [database/seeders/DemoSeeder.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/database/seeders/DemoSeeder.php)  
  *Menghindari duplicate insert pada tabel `kelas` dan `siswa` dengan memanggil `find($id)->update()` untuk model pasangannya.*

### Pengujian (Test Suite)
- [tests/Feature/Evaluation/GradeCalculatorTest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/tests/Feature/Evaluation/GradeCalculatorTest.php)  
  *Memperbaiki setup TahunAjaran dengan melakukan retrieval/update alih-alih duplicate creation.*
- [tests/Feature/Evaluation/RaporGeneratorTest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/tests/Feature/Evaluation/RaporGeneratorTest.php)  
  *Memperbaiki setup TahunAjaran dan Student dengan melakukan retrieval/update alih-alih duplicate creation.*

### Validasi Kelas (`classrooms` $\rightarrow$ `kelas`)
- [app/Http/Requests/StoreClassroomRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Http/Requests/StoreClassroomRequest.php)  
  *Mengubah `Rule::unique('classrooms')` menjadi `Rule::unique('kelas')`.*
- [app/Http/Requests/StoreScheduleRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Http/Requests/StoreScheduleRequest.php)  
  *Mengubah `exists:classrooms,id` menjadi `exists:kelas,id`.*
- [app/Http/Requests/StoreFormativeAssessmentRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Http/Requests/StoreFormativeAssessmentRequest.php)  
  *Mengubah `exists:classrooms,id` menjadi `exists:kelas,id`.*
- [app/Http/Requests/StoreSummativeAssessmentRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Http/Requests/StoreSummativeAssessmentRequest.php)  
  *Mengubah `exists:classrooms,id` menjadi `exists:kelas,id`.*
- [app/Modules/Evaluation/Requests/BatchGradeRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Evaluation/Requests/BatchGradeRequest.php)  
  *Mengubah `exists:classrooms,id` menjadi `exists:kelas,id`.*
- [app/Modules/Evaluation/Controllers/GradeEntryController.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Evaluation/Controllers/GradeEntryController.php)  
  *Mengubah inline request validation `exists:classrooms,id` menjadi `exists:kelas,id`.*

### Validasi Siswa (`students` $\rightarrow$ `siswa`)
- [app/Http/Requests/StoreStudentViolationRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Http/Requests/StoreStudentViolationRequest.php)  
  *Mengubah `exists:students,id` menjadi `exists:siswa,id`.*
- [app/Http/Requests/StoreStudentSavingRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Http/Requests/StoreStudentSavingRequest.php)  
  *Mengubah `exists:students,id` menjadi `exists:siswa,id`.*
- [app/Http/Requests/StoreStudentPaymentRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Http/Requests/StoreStudentPaymentRequest.php)  
  *Mengubah `exists:students,id` menjadi `exists:siswa,id`.*
- [app/Http/Requests/StoreStudentCounselingRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Http/Requests/StoreStudentCounselingRequest.php)  
  *Mengubah `exists:students,id` menjadi `exists:siswa,id`.*
- [app/Http/Requests/StoreStudentAchievementRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Http/Requests/StoreStudentAchievementRequest.php)  
  *Mengubah `exists:students,id` menjadi `exists:siswa,id`.*
- [app/Http/Controllers/Finance/StudentBillController.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Http/Controllers/Finance/StudentBillController.php)  
  *Mengubah `exists:students,id` menjadi `exists:siswa,id` pada fungsi `store()`.*
- [app/Modules/Evaluation/Requests/BatchGradeRequest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Evaluation/Requests/BatchGradeRequest.php)  
  *Mengubah `exists:students,id` menjadi `exists:siswa,id`.*

---

## 3. Hasil Pengujian & Verifikasi

### A. Migrasi & Seeding Berhasil
Perintah migrasi fresh dan seeding berhasil berjalan 100% tanpa error duplikasi primary key:
```powershell
php83 artisan migrate:fresh --seed
```
Output log menunjukkan seluruh seeder (`Database\Seeders\DemoSeeder` dkk) sukses dieksekusi dengan status `DONE`.

### B. Test Suite Berhasil 100%
Seluruh pengujian unit dan fitur telah selesai dengan sukses:
```powershell
php83 artisan test
```
**Hasil:** `Tests: 115 passed (289 assertions)` - Hijau/Green 100%!

### Catatan Terkait Lock Database (PENTING)
> [!IMPORTANT]
> Menjalankan perintah database seperti `php83 artisan migrate:fresh` secara manual di terminal ketika proses pengujian otomatis (`php83 artisan test`) sedang berjalan di background dapat memicu error database locks atau kegagalan pembuatan tabel (misal: `Base table or view already exists: Table 'users' already exists`).
> Hal ini disebabkan karena transaksi/locks yang terjadi selama pengujian. Pastikan proses test runner selesai sepenuhnya sebelum melakukan migrasi ulang database secara manual.
