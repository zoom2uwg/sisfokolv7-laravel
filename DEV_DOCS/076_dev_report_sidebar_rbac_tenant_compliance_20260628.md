# Dev Report: Audit & Verifikasi Kepatuhan RBAC & Tenant Sidebar

**Tanggal:** 28 Juni 2026  
**Oleh:** Antigravity  
**Status:** ⚠️ Temuan Kebocoran Akses (Non-Compliant pada Tenant Admin)

---

## 1. Pendahuluan

Untuk menindaklanjuti verifikasi runtime Sisfokol v7, telah dilakukan **Browser E2E Audit** menggunakan Browser Subagent otomatis untuk memeriksa kepatuhan visual menu Sidebar terhadap aturan **RBAC (Role-Based Access Control)** dan **Tenant Isolation boundaries** (ADR-003 & ADR-010).

Pengujian dilakukan dengan masuk sebagai masing-masing dari 8 role pengguna, mengekstraksi daftar menu navigasi yang dirender di Sidebar, memverifikasi URL/route, dan mendokumentasikan hasilnya.

---

## 2. Hasil Audit Menu Sidebar

| No | Pengguna / Akun | Role (Spatie) | Menu yang Terlihat di Sidebar | Status Kepatuhan | Temuan / Catatan |
| :--- | :--- | :--- | :--- | :---: | :--- |
| **1** | `superadmin` | `super_admin` | Dashboard, Tenants, Branches, Pengguna, RBAC Builder, Audit Log, Plugin | **COMPLIANT** | Berfungsi sebagai Super Admin global (tanpa tenant). |
| **2** | `admin` | `admin` | Dashboard, Tenants, Branches, Pengguna, RBAC Builder, Audit Log, Plugin | **COMPLIANT** | Berfungsi sebagai Global Admin Sekolah (tenant_id = null). |
| **3** | `admin.sekolah` | `admin` | Dashboard, **Tenants (#)**, **Branches (#)**, Pengguna, **RBAC Builder**, **Audit Log**, **Plugin**, Siswa, Guru | <span style="color:red">**NON-COMPLIANT**</span> | **BOCOR (Leak):** Menampilkan menu global platform (`Tenants`, `Branches`, `RBAC Builder`, `Audit Log`, `Plugin`). Meskipun menu Tenants/Branches mengarah ke `#` karena rutenya tidak terdaftar di web, ini tetap merupakan kebocoran visual. **[FIXED 2026-06-28]** — lihat §6. Menu yang diharapkan setelah perbaikan: Dashboard, Pengguna, Siswa, Guru (menu platform disembunyikan via flag `is_platform`). |
| **4** | `piket.demo` | `picket-officer` | Dashboard, Presensi, Absensi | **COMPLIANT** | Sesuai porsi kerja. Hanya menampilkan menu presensi & absensi. |
| **5** | `bk.demo` | `counselor` | Dashboard, Siswa, Presensi, Absensi | **COMPLIANT** | Hanya menampilkan menu siswa & absensi. |
| **6** | `guru.demo` | `teacher` | Dashboard, Siswa, Guru, Kelas, Mapel, Jadwal, Presensi | **COMPLIANT** | Hanya menampilkan menu akademik sekolah & presensi. |
| **7** | `walikelas.demo` | `homeroom-teacher`| Dashboard, Siswa, Guru, Kelas, Mapel, Jadwal, Presensi | **COMPLIANT** | Menampilkan menu akademik sekolah & presensi untuk perwalian kelas. |
| **8** | `siswa.2024001` | `student` | Dashboard, Jadwal, Presensi, Absensi | **COMPLIANT** | Menu sangat terbatas untuk portal mandiri siswa. |

---

## 3. Akar Masalah Kebocoran Menu `admin.sekolah` (Analisis Teknis)

Kebocoran menu global platform pada role **Tenant Admin (`admin.sekolah`)** terjadi akibat interaksi antara tiga faktor berikut:

### A. Penugasan Role `admin`
Pada `DemoSeeder.php` (Line 151), pengguna `admin.sekolah` diberikan role bernama `admin`:
```php
$user->assignRole($u['role']); // di mana $u['role'] = 'admin'
```
Role `admin` dalam `RolePermissionSeeder.php` memiliki wildcard permission `*` (memiliki semua hak akses).

### B. Logical Check `MenuRenderer.php`
Di `app/Support/MenuRenderer.php`, logika penyaringan menu adalah sebagai berikut:
```php
$query = Menu::where('aktif', true)->orderBy('urutan');
if (! $user->isSuperAdmin()) {
    $query->where(function ($q) use ($user) {
        $q->whereNull('tenant_id')->orWhere('tenant_id', $user->tenant_id);
    });
}
```
Karena menu-menu platform global seperti `Tenants`, `Branches`, `RBAC Builder`, `Audit Log`, dan `Plugin` disimpan dengan `tenant_id = null` di database (bersifat sistem global), kondisi `whereNull('tenant_id')` secara otomatis menyertakan menu-menu global tersebut ke dalam daftar menu untuk user non-superadmin.

Selanjutnya, proses penyaringan hak akses:
```php
// Filter by permission
if (! $user->isSuperAdmin()) {
    $menus = $menus->filter(function ($m) use ($user) {
        if (! $m->permission_required) return true;
        return $user->can($m->permission_required);
    });
}
```
Karena `admin.sekolah` memiliki role `admin` dengan wildcard permission `*`, panggilan `$user->can($m->permission_required)` akan selalu menghasilkan `true` untuk semua menu global (seperti `tenant.view`, `plugin.activate`, dll.).

---

## 4. Solusi Rekomendasi untuk Perbaikan

Untuk menyelesaikan kebocoran visual ini dan merapatkan isolasi Tenant Admin:

1. **Pemisahan Role Admin Global & Tenant:**
   Bedakan role `admin` (global system admin) dengan role `tenant_admin` (admin sekolah/tenant saja). Role `tenant_admin` tidak boleh diberi wildcard permission `*` global, melainkan hanya subset permission yang relevan dengan level tenant (tanpa `tenant.view`, `plugin.activate`, `rbac.manage`, `audit.view` global).
   
2. **Koreksi Logika Query MenuRenderer:**
   Menu global platform (seperti `Tenants` dan `Branches`) harus secara eksplisit ditandai sebagai menu level platform/system, sehingga user yang memiliki `tenant_id !== null` (bukan SuperAdmin) tidak akan dapat mengambil menu-menu global tersebut sekalipun mereka memiliki permission bypass `*`.

---

## 5. Lampiran Bukti Capture

Semua bukti screenshot baru tersimpan di folder [DEV_DOCS/assets/](file:///d:/laragon/www/sisfokolv7/DEV_DOCS/assets):

* **Super Admin Dashboard:** `assets/076_superadmin_dashboard.png`
* **Global Admin Dashboard:** `assets/076_admin_dashboard.png`
* **Tenant Admin (Non-Compliant):** `assets/076_admin_sekolah_dashboard.png` (memperlihatkan menu platform global yang bocor)
* **Guru Piket:** `assets/076_piket_dashboard.png`
* **Guru BK:** `assets/076_bk_dashboard.png`
* **Guru Mapel:** `assets/076_guru_dashboard.png`
* **Wali Kelas:** `assets/076_walikelas_dashboard.png`
* **Siswa:** `assets/076_siswa_dashboard.png`

---

## 6. Perbaikan (2026-06-28)

Kebocoran menu `admin.sekolah` telah diperbaiki dengan menerapkan **Rekomendasi #2** (penandaan eksplisit menu level platform), yang berakar pada prinsip database-driven ADR-010:

1. **Migrasi baru** `app/Modules/Auth/Database/Migrations/2026_06_28_000000_add_is_platform_to_menus_table.php`
   menambah kolom boolean `is_platform` pada tabel `menus` dan backfill idempoten untuk 5 menu platform global:
   `tenancy.tenants`, `tenancy.branches`, `auth.rbac`, `auth.audit`, `auth.plugins`.

2. **`MenuSeeder.php`** menandai kelima menu tersebut dengan `is_platform => true` (seeding segar).

3. **`app/Support/MenuRenderer.php`** mengecualikan menu `is_platform = true` pada query untuk user non-SuperAdmin
   (`tenant_id !== null`) — *sebelum* filter permission berjalan, sehingga wildcard `*` pada role `admin` tidak
   lagi membuka jalan ke menu platform. Mempertahankan rule SuperAdmin → semua menu (`test_superadmin_still_sees_platform_menus`).

4. **Test regressi** `tests/Feature/Rbac/MenuRendererTest.php`:
   - `test_tenant_admin_with_wildcard_does_not_see_platform_menus` — mereproduksi skenario `admin.sekolah` (role `admin`, wildcard `*`) dan memastikan kelima menu platform tidak tampil, sementara menu tenant (Dashboard, Siswa, Guru) tetap tampil.
   - `test_superadmin_still_sees_platform_menus` — memastikan SuperAdmin tetap melihat menu platform (no regression).

**Hasil verifikasi:** `php artisan test --filter="Rbac"` → 33 passed (60 assertions), 0 failure.
