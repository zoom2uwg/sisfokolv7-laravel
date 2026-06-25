# Panduan Database Seeder

**Dibuat:** 2026-06-25
**Author:** ZCode

## Urutan Seeder yang Harus Dijalankan

Berikut urutan **WAJIB** saat menjalankan seeder secara manual:

| No | Seeder | Keterangan | Dependensi |
|----|--------|------------|------------|
| 1 | `RolePermissionSeeder` | Buat roles & permissions (admin, super_admin, dll) | - |
| 2 | `SuperAdminSeeder` | Buat user super admin | RolePermissionSeeder |
| 3 | `SchoolProfileSeeder` | Profil sekolah | - |
| 4 | `AcademicYearSeeder` | Tahun ajaran | - |
| 5 | `DaySeeder` | Hari (Senin-Sabtu) | - |
| 6 | `HourSeeder` | Jam pelajaran | - |
| 7 | `TimeSlotSeeder` | Slot waktu | HourSeeder |
| 8 | `SubjectTypeSeeder` | Jenis mata pelajaran | - |
| 9 | `AttendanceTimeSeeder` | Waktu absensi | - |
| 10 | `UserSeeder` | User lainnya | RolePermissionSeeder |
| 11 | `DemoSeeder` | Data demo (tenant, siswa, jadwal, dll) | **RolePermissionSeeder, DaySeeder, TimeSlotSeeder** |
| 12 | `ClassroomSeeder` | Data kelas | DemoSeeder |
| 13 | `MenuSeeder` | Menu navigasi | RolePermissionSeeder |
| 14 | `FieldSeeder` | Field ACL | - |

## DemoSeeder Error? Ini Penyebabnya

### Error 1: `RoleDoesNotExist - There is no role named 'admin'`

**Penyebab:** `RolePermissionSeeder` belum dijalankan.

**Solusi:**
```bash
php83 artisan db:seed RolePermissionSeeder
php83 artisan db:seed DemoSeeder
```

### Error 2: `Foreign key constraint fails (schedules.day_id -> days.id)`

**Penyebab:** `DaySeeder` belum dijalankan. Tabel `days` kosong.

**Solusi:**
```bash
php83 artisan db:seed DaySeeder
php83 artisan db:seed DemoSeeder
```

### Error 3: `Duplicate entry for key 'tenants_npsn_unique'`

**Penyebab:** Data demo sudah ada di database.

**Solusi:** Reset database terlebih dahulu:
```bash
php83 artisan migrate:fresh --seed
```

## Cara Menjalankan Seeder

### Opsi 1: Semua Seeder Sekaligus (RECOMMENDED)

```bash
# Reset database + jalankan semua seeder urut
php83 artisan migrate:fresh --seed
```

Perintah ini akan:
1. Drop semua tabel
2. Jalankan semua migrasi
3. Jalankan `DatabaseSeeder` (urutan sudah benar)

### Opsi 2: Jalankan DatabaseSeeder Saja

```bash
# Tanpa reset database
php83 artisan db:seed
```

Perintah ini menjalankan `DatabaseSeeder` yang sudah punya urutan benar.

### Opsi 3: Seeder Manual (Satu-satu)

Jika harus manual, ikuti urutan di tabel atas:

```bash
php83 artisan db:seed RolePermissionSeeder
php83 artisan db:seed SuperAdminSeeder
php83 artisan db:seed SchoolProfileSeeder
php83 artisan db:seed AcademicYearSeeder
php83 artisan db:seed DaySeeder
php83 artisan db:seed HourSeeder
php83 artisan db:seed TimeSlotSeeder
php83 artisan db:seed SubjectTypeSeeder
php83 artisan db:seed AttendanceTimeSeeder
php83 artisan db:seed UserSeeder
php83 artisan db:seed DemoSeeder
php83 artisan db:seed ClassroomSeeder
php83 artisan db:seed MenuSeeder
php83 artisan db:seed FieldSeeder
```

## Catatan Penting

1. **Jangan jalankan `DemoSeeder` sebelum:**
   - `RolePermissionSeeder` (untuk role 'admin')
   - `DaySeeder` (untuk foreign key schedules.day_id)
   - `TimeSlotSeeder` (untuk foreign key schedules.time_slot_id)

2. **Jika database sudah ada data demo:**
   - Gunakan `migrate:fresh --seed` untuk reset total
   - Atau hapus manual data yang duplicate sebelum re-seed

3. **Urutan di `DatabaseSeeder.php` sudah benar:**
   - Lihat file: `database/seeders/DatabaseSeeder.php`
   - Tinggal panggil `php83 artisan db:seed` tanpa parameter

## Quick Reference

```bash
# RESET TOTAL (rekomendasi)
php83 artisan migrate:fresh --seed

# CEK STATUS MIGRASI
php83 artisan migrate:status

# LIHAT SEEDER TERSEDIA
dir database\seeders
```
