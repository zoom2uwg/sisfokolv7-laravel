# DEV_DOCS-073: Dev Report — Desain Role Waka (Wakil Kepala Sekolah)

- **Tanggal:** 2026-06-28
- **Penulis:** ZCode Agent
- **Proyek:** SISFOKOL v7 → Laravel 11 (`sisfokol-laravel/`)
- **Jenis:** Dev Report — Desain (DESAIN, **belum implementasi**)
- **Status:** Desain selesai, menunggu approval user → lanjut writing-plans
- **Metode:** Brainstorming skill (explore → Q&A → approaches → design). Setiap keputusan via AskUserQuestion. Mapping permission berbasis policy nyata (dibaca dari codebase, bukan asumsi).

---

## 1. Latar Belakang & Pemicu

Proyek memerlukan **Role khusus untuk Waka (Wakil Kepala Sekolah)** karena ada beberapa Bidang yang perlu dibedakan wewenangnya:

1. **Waka Kurikulum dan Akademik**
2. **Waka Kesiswaan, Organisasi dan PPDB**
3. **Waka Sarana Prasarana dan Humas**

Saat ini belum ada role Waka di codebase (hanya `principal` = Kepala Sekolah, view-only). Perlu menambahkan struktur Waka yang memetakan ke modul yang sudah ada.

---

## 2. Ground Truth RBAC (Hasil Eksplorasi)

Diverifikasi fisik dari codebase (bukan klaim):

### 2.1 Role yang sudah ada (10 role)
`super_admin`, `admin`, `principal`, `teacher`, `student`, `homeroom-teacher`, `finance`, `counselor`, `picket-officer`, `inventory`.
- Didefinisikan di `database/seeders/RolePermissionSeeder.php` (array `$roles`, baris 102-202).
- Tidak ada string "waka"/"wakil" di mana pun → **aman ditambahkan tanpa bentrok**.

### 2.2 Pola gating permission (KRITIS — terverifikasi dari policy nyata)
Crudlfix trait (`app/Support/Crudlfix/Crudlfix.php`) punya 2 mode authorize: `policy` (Gate→Policy) dan `permission` (`{authorize}.{action}`).

**Pola berlapis yang ditemukan:**
- **Menu visibility** = Indonesian `.view` (`siswa.view`, `kelas.view`, …) — dipakai `MenuSeeder` + difilter `MenuRenderer::forUser()` via `$user->can($m->permission_required)`.
- **Data access (policy)** = English `student.*`/`student.view`, `master.classroom.*`, dll — dipakai di `*Policy.php`.
- **Untuk benar-benar pakai modul, role butuh KEDUANYA.**

Bukti nyata:
- `SiswaPolicy`: `viewAny` = `student.* || student.view`; `create/update/delete` = `student.*`.
- `KelasPolicy`: `viewAny` = `master.classroom.* || student.view || employee.view`; `create/update/delete` = `master.classroom.*`.
- Role `principal` punya `student.view` (policy) **+** `siswa.view` (menu) — konfirmasi pola berlapis.

### 2.3 Modul/Domain yang ada
- `app/Modules/Academic` — siswa, guru, kelas, mapel, jadwal, tahun ajaran, orang tua
- `app/Modules/Evaluation` — penilaian/rapor
- `app/Modules/Finance` — tagihan, pembayaran, tabungan
- `app/Modules/Presence` — absensi, izin, pelanggaran, BK, prestasi
- `app/Plugins/Kurikulum` — kurikulum, struktur, komponen kompetensi (permission `kurikulum.view`/`kurikulum.manage`)
- `inventory.*` permission ada (modul Inventory belum jadi direktori)

**Yang TIDAK ada:** modul PPDB, modul Humas, modul Sarana Prasarana (hanya `inventory.*`).

### 2.4 Tenant
Role di-seed global (team_id NULL, `Role::findOrCreate($name,'web')`). Runtime pakai team context per-tenant via `setPermissionsTeamId`. **Tanpa migrasi** untuk tambah role.

---

## 3. Keputusan Desain (via AskUserQuestion)

| # | Pertanyaan | Keputusan |
|---|---|---|
| 1 | Cara model Waka + 3 bidang | **3 role terpisah** (`waka-kurikulum`, `waka-kesiswaan`, `waka-sarpras`) — ikut pola existing (`homeroom-teacher`, `picket-officer`). Tanpa migrasi. |
| 2 | Tingkat wewenang Waka atas bidang | **Manage bidang + view lain** — CRUD penuh di bidang sendiri, view-only di bidang lain. Waka = pemilik operasional bidang. |
| 3 | Bidang yg modulnya belum ada (PPDB/Humas/Sarpras) | **3 role, permission modul ada saja** — assign manage hanya utk modul yg sudah ada. Tidak bikin permission hantu. YAGNI. |

---

## 4. Mapping Permission per Role (Berbasis Policy Nyata)

> Konvensi: `manage` = policy `.*` (CRUD create/update/delete); `view` = pasangan menu `.view` + policy `.view`. Setiap modul butuh keduanya agar menu muncul **dan** data bisa diakses.

### 4.1 `waka-kurikulum` — Kurikulum & Akademik

| Aksi | Modul | Permission |
|---|---|---|
| Manage | Kurikulum (plugin) | `kurikulum.manage` + `kurikulum.view` |
| Manage | Mapel | `master.subject.*` + `master.subject-type.*` + `mapel.view` |
| Manage | Kelas | `master.classroom.*` + `kelas.view` |
| Manage | Tahun Ajaran | `master.academic-year.*` |
| Manage | Ruang | `master.room.*` |
| Manage | Ekskul | `master.extracurricular.*` |
| Manage | Jadwal | `academic.schedule.*` + `jadwal.view` |
| Manage | Kurikulum akademik | `academic.curriculum.*` + `academic.teacher-agenda.*` |
| View | Siswa | `student.view` + `siswa.view` |
| View | Guru | `employee.view` + `guru.view` |
| View | Presensi/Absensi | `presence.view`+`presensi.view`, `absence.view`+`absensi.view` |
| View | Keuangan | `finance.student-bill.view`+`tagihan.view`, `finance.student-payment.view`+`pembayaran.view`, `tabungan.view` |
| View | Rapor | `raport.view` |
| Umum | Dashboard/Laporan/Profil | `dashboard.view`, `report.*`, `master.school-profile.view` |

### 4.2 `waka-kesiswaan` — Kesiswaan, Organisasi & PPDB

| Aksi | Modul | Permission |
|---|---|---|
| Manage | Siswa | `student.*` + `siswa.view` |
| Manage | Pelanggaran | `violation.*` + `master.violation-type.*` + `master.violation-point.*` |
| Manage | BK | `counseling.*` + `master.counseling-type.*` |
| Manage | Prestasi | `achievement.*` + `master.achievement-type.*` |
| Manage | Izin | `permit.*` |
| Manage | Ketidakhadiran | `absence.*` + `absensi.view` |
| Manage | Ekskul/Organisasi | `master.extracurricular.*` |
| View | Kurikulum | `kurikulum.view` |
| View | Guru/Kelas/Mapel/Jadwal | `employee.view`+`guru.view`, `kelas.view`, `mapel.view`, `jadwal.view` |
| View | Presensi | `presence.view`+`presensi.view` |
| View | Keuangan | `finance.student-bill.view`+`tagihan.view`, `finance.student-payment.view`+`pembayaran.view`, `tabungan.view` |
| View | Rapor | `raport.view` |
| Umum | Dashboard/Laporan/Profil | `dashboard.view`, `report.*`, `master.school-profile.view` |
| ⏳ Ditunda | PPDB | modul belum ada — tanpa permission |

### 4.3 `waka-sarpras` — Sarana Prasarana & Humas

| Aksi | Modul | Permission |
|---|---|---|
| Manage | Inventaris | `inventory.*` |
| Manage | Ruangan | `master.room.*` |
| Manage | Profil Sekolah | `master.school-profile.update` + `master.school-profile.view` |
| View | Siswa/Guru | `student.view`+`siswa.view`, `employee.view`+`guru.view` |
| View | Akademik | `kelas.view`, `mapel.view`, `jadwal.view` |
| View | Presensi/Keuangan/Rapor/Kurikulum | `presence.view`+`presensi.view`, `absence.view`+`absensi.view`, `finance.student-bill.view`+`tagihan.view`, `finance.student-payment.view`+`pembayaran.view`, `tabungan.view`, `raport.view`, `kurikulum.view` |
| Umum | Dashboard/Laporan | `dashboard.view`, `report.*` |
| ⏳ Ditunda | Humas/Sarpras-lain | modul belum ada — tanpa permission |

> ⚠️ **`waka-sarpras` sengaja tipis** (hanya inventory + ruang + profil sekolah). Ini realita codebase, tidak di-overclaim.

---

## 5. File yang Akan Diubah (Rencana Implementasi)

1. `database/seeders/RolePermissionSeeder.php` — tambah 3 entri di array `$roles` (perubahan utama, ~3 blok).
2. `database/seeders/DemoSeeder.php` — tambah 3 demo user (`waka.kurikulum.demo`, `waka.kesiswaan.demo`, `waka.sarpras.demo`) + record guru/employee terkait.
3. *(opsional, keputusan impl)* Label tampilan role — lihat §6.

Tidak ada migrasi database. Tidak ada file controller/policy/view yang diubah (menu auto-filter via `MenuRenderer::forUser()`).

---

## 6. Gap & Hal Terbuka (Honest Disclosure)

- **Rapor manage tidak tersedia** — seeder tidak punya `raport.*`/`evaluation.*`. Waka-kurikulum hanya bisa **view** rapor. Bila ingin manage rapor, perlu definisi permission baru (di luar scope desain ini).
- **Tabungan view** — tidak ada `finance.student-saving.view` di seeder. Menu `tabungan.view` diberikan, tapi akses policy tabungan perlu verifikasi saat implementasi (mungkin perlu tambahan permission view).
- **Mapel/Jadwal view-only policy belum diverifikasi** — `MapelPolicy`/`JadwalPolicy` belum dibaca. Apakah `viewAny` lolos untuk role tanpa `master.subject.*`? Akan diverifikasi saat implementasi; bila perlu, tambah policy-view permission.
- **Label tampilan role** — Spatie `roles` tidak punya kolom label. Nama kebab-case (`waka-kurikulum`) mungkin tampil apa adanya di UI RBAC. Butuh konvensi label (config map atau kolom baru) — **keputusan implementasi**, bukan desain arsitektur.
- **`waka-kesiswaan` overlap `counselor`** — keduanya manage `counseling.*`/`violation.*`. **Intentional** (Waka membawahi BK) — bukan konflik.
- **Demo user tenant** — demo user Waka perlu `tenant_id` valid + record guru/employee. Detail assignment tenant mengikuti pola `DemoSeeder` existing.

---

## 7. Verifikasi yang Akan Dilakukan Saat Implementasi

1. Setelah seeder dijalankan, cek `php artisan permission:show` / query DB — 3 role terdaftar dengan permission tepat.
2. Login sebagai masing-masing demo user → verifikasi:
   - Menu yang muncul = bidang sendiri (manage) + bidang lain (view)
   - CRUD di bidang sendiri berhasil, di bidang lain 403/deny
3. Verifikasi policy gap (Mapel/Jadwal viewAny, tabungan) — tambah permission bila gagal.
4. Jalankan test suite existing (`php artisan test`) — pastikan tidak ada regresi RBAC.

---

## 8. Status & Langkah Berikutnya

- **Status desain:** SELESAI. Menunggu approval user atas mapping §4.
- **Setelah approval:** invoke `writing-plans` skill untuk susun implementation plan detail (langkah-langkah seeder, demo user, verifikasi).
- **Tidak ada kode ditulis** sebelum approval (HARD-GATE brainstorming skill).

---

## 9. Referensi

- `database/seeders/RolePermissionSeeder.php` (array `$permissions` baris 16-96, `$roles` baris 102-202)
- `app/Support/Crudlfix/Crudlfix.php` (`authorizeCrudlfix` baris 90-122)
- `app/Modules/Academic/Policies/SiswaPolicy.php`, `KelasPolicy.php`
- `app/Support/MenuRenderer.php` (`forUser` baris 20-62)
- `app/Plugins/Kurikulum/permissions.php` (`kurikulum.view`, `kurikulum.manage`)
- `config/permission.php` (`enable_wildcard_permission=true`, `teams=true`)
- DEV_DOCS-072 (review jujur EPIC 7/8/9 — referensi metodologi verifikasi)

---

*Dokumen ini adalah dev report tahap DESAIN berbasis brainstorming. Setiap mapping permission dapat ditelusuri ke policy/seeder nyata. Belum ada perubahan kode yang dilakukan.*
