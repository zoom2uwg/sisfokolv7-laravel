# DEV_DOCS-053a: Ringkasan Konvensi Naming, Namespace, Database & Tabel

- **Tanggal:** 2026-06-25 07:37
- **Topik:** Konsolidasi konvensi penamaan (database/tabel/kolom), namespace PHP, struktur folder modul & plugin, dan naming dokumen ADR/DEV_DOCS
- **Sifat:** Ringkasan referensi cepat (cheat-sheet) bagi agent/developer — semua konvensi di bawah berasal dari ADR yang sudah Accepted
- **Terhubung ke ADR:** 001 (rekam ADR), 002 (rebuild modular), 003 (multi-tenant), 006 (RBAC), 007 (prinsip skema), 008 (DEV_DOCS memory), 009 (plugin contract)
- **Terhubung ke DEV_DOCS:** 003 (skema 48 tabel), 010 (folder structure)

---

## 📦 1. Konvensi Database & Tabel (ADR-007, DEV_DOCS-003)

### Engine & Charset
| Item | Konvensi |
|---|---|
| Engine | **InnoDB** (bukan MyISAM) |
| Charset | `utf8mb4` + collation `utf8mb4_unicode_ci` |
| Helper boilerplate | `$table->tenantAndAuditColumns()` |

### Primary Key & Relasi
| Item | Konvensi | vs Legacy |
|---|---|---|
| PK | `bigIncrements('id')` → `BIGINT UNSIGNED AUTO_INCREMENT` | bukan MD5 `varchar(50)` |
| FK | `unsignedBigInteger('xxx_id')` + foreign constraint | — |
| Kode bisnis (NIS/NIP/kode) | kolom **unique index** biasa, **bukan** PK | — |

### Timestamps & Audit (wajib tiap tabel)
```php
$table->timestamps();              // created_at, updated_at
$table->softDeletes();             // deleted_at
$table->unsignedBigInteger('created_by')->nullable();
$table->unsignedBigInteger('updated_by')->nullable();
// + FK ke users dengan nullOnDelete()
```

### Tipe Data per Domain
| Domain | Tipe |
|---|---|
| Uang/nominal | `decimal(15, 2)` |
| Nilai/skor | `unsignedTinyInteger` (0–100) |
| Poin pelanggaran | `smallInteger` |
| Tahun | `smallInteger` |
| Semester | `tinyInteger` (1–2) |
| Boolean/status | `boolean` |
| Teks panjang | `text` / `longText` (sesuai kebutuhan) |

### FK ON DELETE behavior
- Master data → `RESTRICT` (default)
- Relasi opsional → `nullOnDelete()`
- Child terikat header → `cascadeOnDelete()`

### Konvensi Penamaan Tabel & Kolom (§8 ADR-007) — KRITIS
| Item | Konvensi | Contoh |
|---|---|---|
| Tabel | `snake_case` **plural** | `siswa`, `tagihan_siswa`, `tahun_ajaran` |
| Model | `StudlyCase` **singular** | `Siswa`, `TagihanSiswa`, `TahunAjaran` |
| FK | `<tabel_singular>_id` | `siswa_id`, `tapel_id` |
| Pivot | urutan **alphabetis** | `kelas_siswa`, `siswa_orang_tua` |
| Kolom tenant | `tenant_id` (konsisten di semua tabel domain) | — |

### Tenant Scope (semua tabel domain)
```php
$table->unsignedBigInteger('tenant_id');
$table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
$table->index(['tenant_id', ...kolom_filter_umum]);  // composite index
```
Trait `BelongsToTenant` → global scope `WHERE tenant_id = app('tenant')->id`.

---

## 🗂️ 2. Konvensi Namespace & Struktur Folder (DEV_DOCS-010)

Arsitektur: **Modular Monolith** — `app/Modules/` (core) + `app/Plugins/` (plug-and-play).

### Struktur per Modul
```
app/Modules/<NamaModul>/
├── Controllers/      <NamaDomain>Controller.php
├── Models/           <NamaDomain>.php          (singular, StudlyCase)
├── Policies/         <NamaDomain>Policy.php
├── Requests/         Store<NamaDomain>Request.php / Update<...>Request.php
├── Services/         <Domain>Service.php
├── Observers/        <NamaDomain>Observer.php
├── Database/Migrations/    (migration lokal per modul, bukan global)
├── Resources/views/<kebab-case>/   (di-namespace: view:module::...)
└── routes.php
```

### Modul Core (6)
`Tenancy`, `Auth`, `Academic`, `Evaluation`, `Finance`, `Presence`

### Plugin (9, `app/Plugins/`)
`Kurikulum` (penuh), `Discipline`, `Inventory`, `Tahfidz`, `HafalanHadist`, `BimbinganKonseling`, `PendidikanKarakter`, `PelaporanOrtu`, `PWA` (8 scaffold).

Setiap plugin wajib: `<Nama>Plugin.php` (manifest implement `PluginContract`) + `Providers/<Nama>ServiceProvider.php` + `permissions.php` + `routes.php`.

### Helper cross-cutting → `app/Support/`
`PluginRegistry`, `PluginContract`, `PluginContext`, `FieldAcl`, `MenuRenderer`, `BladeDirectives`, `TenantContext`.

---

## 🔐 3. Konvensi Naming Permission & RBAC (ADR-006)

### Permission
Format: **`<resource>.<aksi>`**

| Aksi standar | Arti |
|---|---|
| `.view` `.create` `.update` `.delete` | CRUD standar |
| `.manage` | shortcut semua aksi |
| `.export` `.approve` `.restore` | khusus |

Contoh: `siswa.create`, `tagihan.view`, `tenant.manage`, `raport.cetak`.

### Role seed (bawaan, `is_system=1`)
`super_admin`, `admin_sekolah`, `ks`, `bendahara`, `bk`, `guru`, `wk`, `piket`, `sarpras`, `siswa`, `ortu`.

### Enforcement 3 lapis
1. Route: `Route::middleware('permission:siswa.create')`
2. Controller: `$this->authorize('create', Siswa::class)`
3. Blade: `@can('siswa.create')`

Spatie **teams mode** aktif → `team_id` = `tenant_id`.

---

## 📝 4. Konvensi Naming Dokumen (ADR-001, ADR-008)

| Kanal | Format | Contoh |
|---|---|---|
| `ADR/` | `0xx_nama_singkat_timestamp.md` (timestamp `YYYYMMDD_HHMM` UTC+7) | `007_prinsip_skema_database_normalisasi_20260620_0647.md` |
| `DEV_DOCS/` | `00x_bagian_topik_timestamp.md` (3 digit urut) | `010_bagian6_folder_structure_tech_deployment_20260620_0850.md` |

Status ADR: `Proposed / Accepted / Superseded / Deprecated`. Bila berubah → `Superseded` + link pengganti (jangan dihapus).

---

## ⚠️ Catatan divergensi yang perlu diperhatikan

1. **Ada 2 file DEV_DOCS 010** (folder structure) dengan timestamp beda — `..._0830` (39KB) dan `..._0850` (30KB). Versi `_0850` lebih baru tapi lebih ringkas. Bila ada perbedaan isi, yang `_0850` lebih otoritatif per urutan waktu.

2. **Ada banyak file dengan nomor sama** (mis. `041_`, `044_`, `049_`, `050_`, `051_`) — nomor ADR/DEV_DOCS di proyek ini **tidak unik**. Untuk referensi presisi, sebut timestamp-nya juga.

3. Dokumen terbaru yang membahas status/gap implementasi: `053_dev_report_migration_dan_validation_fix_20260625.md` (tanggal hari ini).

---

## 📚 Sumber otoritatif (baca bila butuh detail)
- **ADR-007** — prinsip skema database (engine, PK, FK, tipe data, naming tabel/kolom)
- **ADR-006** — RBAC & konvensi permission `resource.aksi`
- **ADR-003** — multi-tenant + `tenant_id` global scope
- **ADR-009** — plugin contract (`PluginContract`, manifest, registry)
- **DEV_DOCS-003** — rincian 48 tabel Fase 1 per modul
- **DEV_DOCS-010** — struktur folder modular + tech stack

## Status dev report 053a: ✅ DISIMPAN
