# DEV_DOCS-047: Implementation Plan — Tahap 2: Infrastruktur API Ready

- **Tanggal:** 2026-06-22
- **Status:** 📅 PLANNING (Akan dieksekusi setelah Tahap 1)
- **Tujuan:** Menyiapkan "pipa" infrastruktur yang kokoh untuk transisi menuju Fase 2 (API-Driven/Mobile/PWA).
- **Terhubung ke:** DEV_DOCS-041, DEV_DOCS-045, ADR-003

---

## ⚡ EXECUTIVE SUMMARY

Setelah integritas data diperbaiki pada Tahap 1, sistem perlu dipersiapkan untuk konsumsi data via API. Saat ini, API aplikasi masih sangat minimalis dan monolitik. 

Tahap 2 fokus pada pembangunan **API Layer** yang terstandarisasi, aman, dan modular, sehingga pengembangan endpoint API di masa depan tidak mengganggu stabilitas sistem Blade-SSR yang sudah ada.

---

## 🛠️ RENCANA IMPLEMENTASI TEKNIS

### 1. Sanctum & CORS (Security Baseline)
**Masalah:** Konfigurasi `.env.example` tidak lengkap dan CORS masih menggunakan default "allow-all" yang tidak aman.
**Action Items:**
- [ ] **Update `.env.example`:** Tambahkan variabel berikut untuk panduan deployment:
    - `SANCTUM_STATEFUL_DOMAINS=sisfokol-laravel.test`
    - `SANCTUM_EXPIRATION=1440`
- [ ] **Konfigurasi CORS:** Jalankan `php artisan config:publish cors` dan sesuaikan `config/cors.php`:
    - Batasi `allowed_origins` hanya untuk domain resmi (produksi/staging).
    - Atur `supports_credentials` ke `true` untuk mendukung session-based API.

### 2. API Resource Layer (Data Transformation)
**Masalah:** Controller saat ini mengembalikan *raw model collection*, yang berisiko mengekspos data sensitif (seperti `password`, `remember_token`) dan tidak fleksibel.
**Action Items:**
- [ ] **Inisialisasi Folder:** Buat struktur `app/Http/Resources/` dengan `.gitkeep`.
- [ ] **Pembuatan Template Resource:** Implementasikan `ScheduleResource.php` sebagai blueprint:
    - Mapping field model $\rightarrow$ JSON key yang bersih.
    - Penanganan relasi (e.g., `classroom_name` alih-alih mengirim seluruh objek `classroom`).
- [ ] **Refactor Controller:** Ubah `Api/ScheduleController@today` untuk mengembalikan `ScheduleResource::collection($schedules)`.

### 3. Modular API Routes (Scalable Routing)
**Masalah:** Semua API saat ini menumpuk di `routes/api.php`. Jika ditambah 100 endpoint, file tersebut akan menjadi tidak terkelola.
**Action Items:**
- [ ] **Update `ModuleServiceProvider`:** Modifikasi logika loading rute agar mendeteksi file `routes_api.php` di tiap folder modul.
- [ ] **Konfigurasi Middleware & Prefix:** Pastikan rute modular API dimuat dengan:
    - Middleware: `['api', 'auth:sanctum']`
    - Prefix: `api/v1`
- [ ] **Migrasi Rute:** Pindahkan rute API spesifik modul dari `routes/api.php` ke `app/Modules/{ModuleName}/routes_api.php`.

### 4. Standardisasi Response & Error Handling (Consistency)
**Masalah:** Format response API tidak konsisten antara satu endpoint dengan endpoint lainnya.
**Action Items:**
- [ ] **Base API Controller/Trait:** Buat `ApiResponder` trait untuk menstandarisasi output JSON:
    - `success($data, $message = null, $code = 200)`
    - `error($message, $code = 400, $details = null)`
- [ ] **Global Exception Handler:** Pastikan error validasi atau `ModelNotFoundException` dikonversi menjadi response JSON yang rapi, bukan halaman HTML error 404/500.

### 5. API Versioning Strategy (Future Proofing)
**Masalah:** Tanpa versioning, perubahan struktur data di masa depan akan memutus koneksi aplikasi mobile yang sudah terinstall di user.
**Action Items:**
- [ ] **Struktur Prefix:** Terapkan pola `/api/v1/...` untuk semua endpoint saat ini.
- [ ] **Dokumentasi Versioning:** Catat dalam ADR bagaimana proses migrasi ke `v2` akan dilakukan tanpa breaking change bagi `v1`.

---

## 🧪 METODE VERIFIKASI (Definition of Done)

Tahap 2 dianggap **SELESAI** jika:

1. **CORS Check:** Request dari domain luar yang tidak terdaftar ditolak oleh server (CORS Error).
2. **Resource Check:** Endpoint `/api/schedules/today` mengembalikan JSON yang sudah difilter oleh `ScheduleResource` (tidak ada field sensitif).
3. **Modular Route Check:** Membuat file `routes_api.php` di salah satu modul $\rightarrow$ Endpoint tersebut otomatis bisa diakses via `/api/v1/...` tanpa menyentuh `routes/api.php`.
4. **Response Check:** Semua error API (misal: ID tidak ditemukan) mengembalikan format JSON konsisten: `{ "status": "error", "message": "...", "code": 404 }`.

---

## ⚠️ RISIKO & MITIGASI

| Risiko | Mitigasi |
| :--- | :--- |
| **Breaking Changes** pada API yang sudah ada | Lakukan pengujian menggunakan Postman/Insomnia sebelum dan sesudah refactor `ScheduleController`. |
| **Overhead Performance** karena Resource Layer | Gunakan `Eager Loading` (`with([...])`) pada controller untuk menghindari masalah $N+1$ query saat proses transformasi Resource. |
| **Konflik Middleware** antara `web` dan `api` | Pastikan `ResolveTenant` middleware terdaftar dengan benar di kedua grup middleware di `bootstrap/app.php`. |
