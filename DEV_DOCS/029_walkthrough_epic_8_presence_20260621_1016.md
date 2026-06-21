# DEV_DOCS-029: Walkthrough — Epic 8: Presence Module (Presensi & Kehadiran)

- **Tanggal:** 2026-06-21 10:16
- **Status:** ✅ SELESAI
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** SISFOKOL v7 Laravel — Modular Monolith

---

## Ringkasan Pekerjaan

Epic 8 mengimplementasikan **modul presensi & kehadiran siswa** secara penuh, mulai dari lapisan database (migrasi), service layer (engine aturan + scanner QR + approval workflow), hingga lapisan presentasi (controllers, policies, dan views Blade).

**Total test: 82 passed / 0 failed / 192 assertions**

---

## Daftar File yang Dibuat / Diubah

### 🗄️ Database Migrations (app/Modules/Presence/Database/Migrations/)
| File | Keterangan |
|------|------------|
| `2026_06_21_000099_add_userable_to_users_table.php` | Tambah kolom `userable_type` & `userable_id` ke tabel `users` (polymorphic) |
| `2026_06_21_000100_alter_attendances_table.php` | Tambah `tenant_id`, `created_by`, `updated_by` ke tabel `attendances` |
| `2026_06_21_000101_alter_absences_table.php` | Tambah `tenant_id`, `created_by`, `updated_by` ke tabel `absences` |
| `2026_06_21_000102_alter_permits_table.php` | Tambah `tenant_id`, `created_by`, `updated_by` ke tabel `permits` |
| `2026_06_21_000103_add_note_to_permits_table.php` | Tambah kolom `note` untuk alasan penolakan izin |

### 🧩 Models (app/Models/)
| File | Perubahan |
|------|-----------|
| `Attendance.php` | Tambah trait `BelongsToTenant`, `TracksAuditColumns`, relasi polymorphic `attendable()` |
| `Absence.php` | Tambah trait `BelongsToTenant`, `TracksAuditColumns`, relasi polymorphic `absentable()` |
| `Permit.php` | Tambah trait `BelongsToTenant`, `TracksAuditColumns`, field `note` di fillable |
| `User.php` | Tambah `userable_type`, `userable_id` di fillable, relasi `userable()` |

### ⚡ Events & Observers (app/Modules/Presence/)
| File | Keterangan |
|------|------------|
| `Events/PresenceRecorded.php` | Event yang di-fire setiap scan QR berhasil |
| `Observers/AttendanceObserver.php` | Observer untuk log audit kehadiran |

### 🔧 Services
| File | Keterangan |
|------|------------|
| `app/Modules/Presence/Services/PresensiRuleEngine.php` | Engine penilaian status kehadiran (`present`, `late`, `early`) berdasar `AttendanceTime` |
| `app/Modules/Presence/Services/QrScannerService.php` | Proses scan QR → validasi siswa per-tenant → anti-duplikasi → catat `Attendance` |
| `app/Modules/Presence/Services/IzinApprovalService.php` | Workflow pengajuan izin (`submit`) + persetujuan (`approve`) + penolakan (`reject`) |

### 🛡️ Policies
| File | Keterangan |
|------|------------|
| `app/Modules/Presence/Policies/PresensiPolicy.php` | Otorisasi CRUD `Attendance` via permission `presence.*` |
| `app/Modules/Presence/Policies/IzinPolicy.php` | Otorisasi CRUD + `approve` `Permit` hanya untuk role `picket-officer` / `counselor` |

### 🎮 Controllers (app/Modules/Presence/Controllers/)
| File | Endpoints |
|------|-----------|
| `PresensiController.php` | `GET /presence/scan`, `POST /presence/scan`, `GET /presence/rekap` |
| `AbsensiController.php` | `GET/POST /presence/absensi`, `DELETE /presence/absensi/{id}` |
| `IzinController.php` | Full CRUD + `POST /presence/izin/{id}/approve` + `POST /presence/izin/{id}/reject` |
| `LaporanPresensiController.php` | `GET /presence/laporan` — laporan bulanan dengan grafik tren |

### 🗺️ Routes
| File | Keterangan |
|------|------------|
| `app/Modules/Presence/routes.php` | 15 routes — auto-load via `ModuleServiceProvider` (Presence sudah di `config/modules.php`) |

### 🖼️ Blade Views (resources/views/presence/)
| File | Deskripsi |
|------|-----------|
| `scan.blade.php` | 📷 Scanner QR real-time (html5-qrcode library), manual input fallback, riwayat scan hari ini |
| `rekap.blade.php` | 📊 Daftar rekap kehadiran dengan filter tanggal & status |
| `laporan.blade.php` | 📈 Laporan bulanan: 4 stat cards, grafik batang harian, top 10 siswa kehadiran terendah |
| `izin/index.blade.php` | 📋 Daftar izin dengan filter status & tanggal |
| `izin/create.blade.php` | 📝 Form pengajuan izin (siswa, tanggal, jenis, alasan, lampiran file) |
| `izin/show.blade.php` | 🔎 Detail izin + tombol approve/reject dengan form alasan penolakan (Alpine.js) |

### ⚙️ Provider
| File | Perubahan |
|------|-----------|
| `app/Providers/AppServiceProvider.php` | Daftarkan `PresensiPolicy` dan `IzinPolicy` |

---

## Test Coverage

```
Tests\Feature\Presence\QrScanTest         ✅ 4/4 passed
  ✓ qr scan successfully creates attendance
  ✓ qr scan marks as late if after threshold
  ✓ qr scan prevents duplicate scans on same day
  ✓ qr scan enforces tenant isolation

Tests\Feature\Presence\IzinApprovalTest  ✅ 4/4 passed
  ✓ picket officer can submit izin for siswa
  ✓ counselor can approve izin
  ✓ izin can be rejected
  ✓ cannot approve already processed izin

Full Suite: 82 passed / 192 assertions / 0 failures
```

---

## Arsitektur Keputusan

1. **Multi-Tenant Isolation**: Semua query `Attendance`, `Absence`, `Permit` otomatis di-scope ke `tenant_id` via trait `BelongsToTenant`.

2. **QR Scanner Anti-Spam**: `QrScannerService::scan()` menjalankan pemeriksaan duplikasi (`Attendance::exists()`) di dalam `DB::transaction()` untuk mencegah race condition.

3. **Approval Workflow State Machine**: `IzinApprovalService` menjaga integritas state — hanya izin berstatus `pending` yang dapat diproses, melempar `Exception` jika sudah `approved`/`rejected`.

4. **Policy-Based Authorization**: Seluruh controller menggunakan `Gate::authorize()` — tidak ada proteksi berbasis middleware role saja.

5. **Routes via ModuleServiceProvider**: `Presence` sudah terdaftar di `config/modules.php['core']`, sehingga `routes.php` otomatis di-load tanpa perlu edit `bootstrap/app.php`.

---

## Langkah Verifikasi Manual

1. Login sebagai `picket-officer` → buka `/presence/scan` → aktifkan kamera → scan QR siswa → verifikasi status `present`/`late`
2. Buka `/presence/izin/create` → ajukan izin sakit → verifikasi status `pending`
3. Login sebagai `counselor` → buka `/presence/izin/{id}` → setujui/tolak → verifikasi status berubah
4. Buka `/presence/laporan` → pilih bulan → verifikasi stats cards dan grafik tren
