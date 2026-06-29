# Analisis: Adopsi Livewire + Crudlfix di SISFOKOL v7

**Tanggal:** 29 Juni 2026
**Penulis:** ZCode (sesi analisis adopsi Livewire)
**Branch:** `main`
**Status:** ANALISIS [CMD-VERIFY via grep fisik codebase + cross-check ADR/DEV_DOCS]

---

## ⚠️ Catatan untuk agent lain — BACA DULU SEBELUM ACT

Dokumen ini analisis **status quo adopsi Livewire** per 29 Jun 2026. Metode: silang-validasi
3 sumber — (1) grep fisik codebase, (2) changelog ADR-011 v1→v2, (3) DEV_DOCS 071/075/076/079.
Tidak ada perubahan kode. **Jangan hapus layer Livewire Crudlfix** — terverifikasi AKTIF dipakai
11 view (bdk §2). Dokumen ini melengkapi (bukan menggantikan) doc 076/079.

---

## 1. Ringkasan Eksekutif

**Verdict: Penerapan Livewire = SEBAGIAN (parsial), dan HAL INI BY DESIGN — bukan migrasi yang tertinggal.**

Namun secara fungsional, adopsi baru mencapai **25% halaman CRUD** dan terkonsentrasi di **satu modul** (Academic). Arsitektur aslinya (ADR-011 v1, 22 Jun) memang **tidak memakai Livewire sama sekali**; Livewire ditambahkan 4 hari kemudian (26 Jun) sebagai **hybrid selektif untuk CRUD saja**. Jadi ini pilihan arsitektural sadar, bukan target yang gagal dicapai.

| Dimensi | Temuan | Skor |
|---------|--------|------|
| Luas adopsi | 11/44 halaman CRUD (25%); 0 full-page route; 1 modul inti | Parsial |
| Efektivitas | UX win nyata di list/form sederhana; banyak gap fitur (show, cascade, export) | Cukup |
| Efisiensi dev | Cepat untuk CRUD generik baru (~1,25 hari/modul); beban dual-maintenance | Campuran |
| Kesesuaian lapangan | Scope selektif cocok domain sekolah; inkonsistensi UX antar-modul jadi risiko | Baik dgn catatan |

---

## 2. Sejauh Mana Penerapan? — Data Ground Truth

### Dari sisi infrastruktur: 100% siap

Stack TALL **lengkap terinstall** (`composer.json` Livewire ^4.3; `layouts/app.blade.php` inject `@livewireStyles`/`@livewireScripts`). Diverifikasi doc 071.

### Dari sisi fungsional: 25% adopsi, very narrow scope

Grep fisik seluruh codebase (bukan klaim dokumen):

| Metrik | Angka | Bukti |
|--------|-------|-------|
| Halaman `index.blade.php` (CRUD list) | 44 | `find resources/views -name index.blade.php` |
| Yang memakai `@livewire` | **11** | grep `@livewire\|<livewire:` |
| **Adopsi Livewire** | **25%** | 11/44 |
| Full-page Livewire route | 0 | grep `Route::.*Livewire` → kosong |
| Komponen Livewire (framework) | 7 file | `app/Livewire/Crudlfix/` (Page, Table, Form + 4 traits) |

**Cakupan 11 view Livewire** (10 Academic + 1 Finance):
`academic/{siswa,guru,kelas,kelas-siswa,mapel,mapel-jenis,tahun-ajaran,semester,orang-tua,jadwal}` + `finance/tabungan` — semua `index.blade.php`.

### Yang TIDAK memakai Livewire (75% + seluruh non-CRUD)

- **Semua dashboard** (admin, teacher, student, finance, counselor, picket, principal, homeroom, inventory)
- **Auth & RBAC builder** (menu/role/field ACL, impersonation, audit)
- **Presence** (scan QR pakai Alpine — `presence/scan.blade.php`, absensi, izin, rekap)
- **Evaluation** (grade-entry tabular pakai Alpine, rapor)
- **Finance inti** (pembayaran, tagihan, laporan — transaksional, traditional)
- **Seluruh `app/Http/Controllers/` (37 controller)** — role-based, routes/web.php, **nol Livewire**

### Temuan arsitektural penting: dua hierarki controller paralel

Ada **dua set controller yang saling tumpang-tindih**:

| Sisi | Controller | Routes | Naming | Livewire? |
|------|-----------|--------|--------|-----------|
| `app/Http/Controllers/Admin/` | AcademicYear, Classroom, Subject, Schedule, User... | `routes/web.php` (`/admin/*`) | English | ❌ traditional |
| `app/Modules/Academic/Controllers/` | TahunAjaran, Kelas, Mapel, Jadwal, Siswa... | `Modules/Academic/routes.php` (`/academic/*`) | Indonesian | ✅ 10 view Livewire |

`AcademicYear` ≈ `TahunAjaran`, `Classroom` ≈ `Kelas`, `Subject` ≈ `Mapel`, `Schedule` ≈ `Jadwal` — entitas yang sama diimplementasi dua kali, satu traditional satu Livewire. Sumber kompleksitas nyata (bukan murni masalah Livewire, tapi memperberat beban hybrid).

### Catatan penting: `use Crudlfix;` trait ≠ view Livewire

17 controller memakai trait `Crudlfix`, tapi trait itu menyediakan **baik** method HTTP traditional (index/create/edit) **maupun** `getCrudlfixConfig()` untuk Livewire. `AbsensiController` + 3 controller Kurikulum pakai trait tapi render **view traditional**. Jadi patokan adopsi Livewire yang benar = **view yang memakai `@livewire` (11)**, bukan jumlah controller ber-trait (17).

---

## 3. Efektivitas

### ✅ Yang benar-benar tercapai (terverifikasi)

- **Real-time validation** — error saat mengetik (`wire:model.blur`), bukan setelah submit
- **No-reload search/sort/paginate** — UX jauh lebih halus di list master-data
- **Inline edit tanpa 404** — `CrudlfixTable.editRecord()` dispatch event → `CrudlfixPage.setMode('edit')`. Sebelumnya edit pakai `<a href>` → 404 untuk controller tanpa `edit.blade.php` (MapelJenis, TahunAjaran)
- **Single source of truth** — arsitektur "controller-resolution" (doc 076 Phase A.2): rules/auth/search/with di-resolve dari controller tiap render, solve masalah `Rule::unique` (closure) yang tidak bisa di-serialize sebagai Livewire property
- **Gap keamanan ditutup** — sebelumnya AJAX Livewire (save/delete/search) **bypass auth controller**; user tanpa permission bisa manipulasi data selama bisa load index. Sekarang `HasCrudlfixAuth` enforce policy/permission di setiap operasi (critical untuk multi-tenant SaaS)

### ⚠️ Gap & limitasi (diakui tim di doc 076/079)

| Gap | Status | Dampak |
|-----|--------|--------|
| Show/detail mode | placeholder ("belum diimplementasikan") | Siswa/Mapel/Kelas fallback ke route `.show` traditional |
| Cascade select | belum didukung Livewire | Jadwal create/edit tetap traditional (Alpine) |
| Search select (AJAX dropdown) | belum | — |
| Export CSV | di-trait tapi belum teruji di konteks Livewire | — |
| Double-query initial load | controller `index()` paginate() LALU Livewire table query sendiri | boros query, bukan bug |
| `Absensi` | dibiarkan traditional | filter kelas/date + bulk-create terlalu custom untuk generic table |
| 2 orphan `*Crudlfix.php` | `SiswaControllerCrudlfix` + `ItemPembayaranControllerCrudlfix`, class-collision risk | HIGH — bisa crash saat `composer dump-autoload --optimize` |

### Cakupan test: kuat di security, lemah di interaktivitas

- **158 test pass / 401 assertion** (suite green)
- Tapi hanya **1 file test Crudlfix** (`CrudlfixRbacTest` — 20 test) yang fokus policy/tenant/auth
- **Tidak ada test interaktif Livewire** (mode-switch, real-time validation) — diakui doc 076 §7: "test suite cakupan policy/tenant, bukan UX interaktif". Dusk belum terinstall

---

## 4. Efisiensi Kerja Developer

### ✅ Efisien untuk CRUD generik baru

- Doc 071 estimasi **~1,25 hari/modul** sederhana (copy pattern `academic/kelas` + sesuaikan config)
- Doc 076 membuktikan: **11 view di-migrate dalam satu sesi** (29 Jun) dengan 0 regresi
- Komponen reusable (`CrudlfixPage/Table/Form` + traits) + guide + golden pattern → onboarding cepat

### ⚠️ Beban yang menggerus efisiensi

1. **Dual maintenance logic** — trait HTTP `Crudlfix` (search/filter/auth) **diduplikasi** di trait Livewire `HasCrudlfixTable/Form/Actions/Auth`. Doc 079 §2.3: "by design, tapi maintenance burden yang harus dijaga sinkron." Ubah di satu sisi, lupa di sisi lain = bug senyap
2. **Dua hierarki controller paralel** (37 `app/Http` + 37 `app/Modules`, fitur tumpang-tindih) — beban kognitif & risiko edit di sisi salah
3. **Iterasi/churn selama migrasi** — doc 079 awalnya salah menyimpulkan layer Livewire = dead code (grep pola tag `<livewire:>` padahal codebase pakai directive `@livewire()`). Self-correction bagus, tapi mengindikasikan tim masih membangun mental model tentang layer hybridnya sendiri
4. **Orphan file & limitasi tertunda** — 2 `*Crudlfix.php` belum dibersihkan (Task 11 tertunda), double-query belum dioptimasi, show mode placeholder. Akumulasi utang teknis kecil
5. **Learning curve** — dev harus paham Livewire + config Crudlfix + aturan fallback (Tier 1 full-page vs Tier 3 index-only vs traditional). Doc 071 flag risiko ini "rendah" karena guide ada, tapi tetap ada

**Singkatnya:** efisiensi TINGGI untuk menambah CRUD master-data baru, tapi efisiensi MAINTENANCE menurun karena duplikasi logic + paralelisme controller.

---

## 5. Kesesuaian dengan Kebutuhan Lapangan

### ✅ Scope selektif cocok dengan domain sekolah

ADR-011 **secara sadar** memilih "Opsi C — Hybrid" **bukan** "Opsi D — Full Livewire". Pertimbangannya masuk akal untuk SIS:

- **CRUD master-data** (siswa/guru/kelas/mapel/jadwal) = pekerjaan repetitif harian admin → live search + no-reload = win nyata
- **Scan presensi** (QR/kamera) → Alpine (bukan Livewire) — benar
- **Grade entry** (tabular kompleks) → Alpine — benar
- **Laporan/print** → Blade SSR (cocok untuk PDF/printable) — benar
- **Pembayaran keuangan** (transaksional) → traditional — masuk akal

Jadi keputusan "Livewire hanya untuk CRUD" **well-reasoned** terhadap workflow sekolah, bukan over-engineering.

### ✅ Siap multi-tenant dari sisi keamanan

Fix auth enforcement (doc 076) + tenant isolation (`resolveModel` enforce 404 anti-data-leakage, ADR-003) + RBAC granular = fondasi aman untuk deployment SaaS multi-sekolah.

### ⚠️ Risiko kesesuaian utama: inkonsistensi UX antar-modul

Karena hanya 25% CRUD (dan 1 modul) yang reactive, **seorang user akan mengalami UX berbeda** antar halaman: di `/academic/siswa` search tanpa reload, tapi di `/admin/users` atau `/finance/pembayaran` search full-reload. Untuk adopsi lapangan, inkonsistensi ini terasa "setengah jadi" di mata user akhir, meski arsitekturnya intentional. Ini **gap kesesuaian terbesar**, bukan teknis.

### ⚠️ Win UX terbatas pada list + form sederhana

Karena show mode placeholder, cascade/search-select belum didukung, dan form kompleks (Jadwal, Tabungan) tetap traditional — maka improvement UX Livewire **dominan di navigasi list**, bukan di entry data kompleks. Untuk sekolah, list/search memang mayoritas aktivitas harian, jadi nilai tetap positif tapi tidak transformasional.

---

## 6. Rekomendasi (berurutan, low-risk)

| # | Aksi | Alasan | Sumber |
|---|------|--------|--------|
| 1 | **Hapus 2 orphan `*Crudlfix.php`** + `composer dump-autoload` | Eliminasi class-collision risk (HIGH) | doc 079 §3.1 |
| 2 | **Override `index()` di migrated controller** skip paginate saat Livewire | Tutup double-query | doc 076 limit #1 |
| 3 | **Implementasi show mode** di `CrudlfixPage` | Saat ini placeholder | doc 076 limit #2 |
| 4 | **Tambah test interaktif** (install Dusk atau Livewire testing) untuk mode-switch + real-time validation | Cakupan test gap di UX | doc 076 §7 |
| 5 | **Putuskan hierarki controller**: konsolidasi `app/Http/Controllers/Admin` ↔ `app/Modules/Academic` (fitur tumpang-tindih) | Kurangi dual-maintenance & beban kognitif | temuan codebase |
| 6 | **Prioritas migrasi modul high-traffic berikutnya** (users, pembayaran) bila ingin konsistensi UX — atau **freeze** dan terima hybrid sebagai final | Tutup gap inkonsistensi UX ATAU sahkan status quo | ADR-011 |

### Posisi strategis

Tim berada di **tipping point**: fondasi Livewire solid + teruji, tapi adopsi masih sempit. Ada dua jalan sah:

- **A. Lanjutkan replikasi** ke modul high-traffic (~6-8 minggu untuk ~80% adopsi, per doc 071) → konsistensi UX meningkat
- **B. Bekukan di 25%**, sahkan hybrid sebagai final architecture, fokus ke cleanup (orphan, double-query, show mode) → stabil, minim risiko

Pilihan B lebih realistis jika prioritas saat ini adalah **stabilisasi & deploy**, bukan ekspansi fitur. Pilihan A jika prioritas adalah **konsistensi pengalaman user** lintas modul.

---

## 7. Metode & Verifikasi

- **Angka adopsi (11/44, 0 full-page route, 7 file komponen)**: grep fisik `resources/views/` + `routes/` + `app/Modules/*/routes.php`
- **Asal-usul keputusan hybrid**: ADR-011 changelog (v1 2026-06-22 Blade+Alpine → v2 2026-06-26 +Livewire CRUD)
- **Status migrasi & limitasi**: DEV_DOCS 071 (analisis Crudlfix vs TALL), 075 (inventaris migrasi), 076 (eksekusi bulk migrasi + Known Limitations), 079 (spaghetti analysis + self-correction)
- **Patokan adopsi**: view yang memakai `@livewire()` directive (bukan jumlah controller ber-trait `use Crudlfix;`), karena trait menyediakan kedua path (HTTP + Livewire)

---

## 8. Referensi silang

- ADR-011 — `ADR/011_ui_architecture_blade_alpine_ssr_20260622.md` (keputusan hybrid)
- DEV_DOCS 071 — `071_analisis_crudlfix_vs_tall_stack_20260628.md` (status adopsi TALL)
- DEV_DOCS 075 — `075_inventaris_rencana_migrasi_livewire_crudlfix_20260628.md` (inventaris migrasi)
- DEV_DOCS 076 — `076_dev_report_bulk_migrasi_livewire_crudlfix_20260629.md` (eksekusi + limit)
- DEV_DOCS 079 — `079_dev_report_fix_menu_leak_dan_analisis_spaghetti_crudlfix_20260629.md` (spaghetti + koreksi)

---

*Generated: 29 Juni 2026. Analisis status quo adopsi Livewire — metode grep fisik + cross-check
dokumentasi tim. Tidak ada perubahan kode pada dokumen ini.*
