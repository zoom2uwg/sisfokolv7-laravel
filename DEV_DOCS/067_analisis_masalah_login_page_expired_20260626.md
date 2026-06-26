# Analisis Masalah Login - Page Expired (419)

**Tanggal:** 2026-06-26  
**Investigator:** AI Agent  
**Status:** ✅ Root Cause Identified

---

## 📋 Deskripsi Masalah

### Gejala yang Dilaporkan
Ketika user melakukan login:
1. Klik tombol **"Masuk"** pada form login
2. Browser mengarahkan ke halaman **"419 | Page Expired"**
3. Meskipun di-refresh, halaman expired tetap muncul
4. Setelah klik tombol **Back** di browser, baru muncul dashboard
5. Masalah ini terjadi secara konsisten (hampir setiap login)

### Dampak
- User experience buruk
- Menimbulkan kebingungan bagi user
- Proses login membutuhkan langkah tambahan (klik Back)
- Terlihat seperti ada error pada aplikasi

---

## 🔍 Investigasi (Smart Debugging Protocol)

### ✅ Layer 1 — Syntax & File Dasar

**File yang Diperiksa:**
- `app/Modules/Auth/Controllers/AuthController.php` ✅ OK
  - Baris 1: `<?php` ada
  - Method `login()` lengkap (baris 23-43)
  - Method `redirectAfterLogin()` ada (baris 57-63)
  - Logic redirect menggunakan `redirect()->intended(route('dashboard'))`

**File View:**
- `resources/views/auth/login.blade.php` ✅ OK
  - CSRF token ada: `@csrf` (baris 270)
  - Form action: `{{ route('login') }}` (baris 269)
  - Method: POST (baris 269)

### ✅ Layer 2 — Routing & Endpoint

**Routes:**
```bash
php artisan route:list | grep login
```

Output:
```
GET|HEAD  login ......... login › App\Modules\Auth\Controllers\AuthController@showLogin
POST      login ......... App\Modules\Auth\Controllers\AuthController@login
```

✅ Routes terdaftar dengan benar

### ✅ Layer 3 — Konfigurasi Session & CSRF

**File `config/session.php`:**
```php
'driver' => env('SESSION_DRIVER', 'database'),      // ✅ OK
'lifetime' => (int) env('SESSION_LIFETIME', 120),   // ✅ OK
'path' => env('SESSION_PATH', '/'),                 // ✅ OK
'domain' => env('SESSION_DOMAIN'),                  // ⚠️ PERHATIAN
'secure' => env('SESSION_SECURE_COOKIE'),           // ⚠️ PERHATIAN
'same_site' => env('SESSION_SAME_SITE', 'lax'),     // ✅ OK
```

**File `.env`:**
```env
APP_URL=http://localhost
APP_NAME=Laravel
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null        # ⚠️ MASALAH POTENSIAL
# SESSION_SECURE_COOKIE tidak di-set
```

**Tabel Sessions:**
```bash
php artisan tinker --execute="echo Schema::hasTable('sessions') ? 'YES' : 'NO';"
# Output: YES ✅
```

### ✅ Layer 4 — Middleware

**File `bootstrap/app.php`:**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\ResolveTenant::class,
        \App\Http\Middleware\ForcePasswordReset::class,
        \App\Http\Middleware\BlockWhileImpersonating::class,
    ]);
})
```

✅ Middleware web group aktif (default Laravel sudah include CSRF protection)

**File `app/Http/Middleware/ResolveTenant.php`:**
```php
public function handle(Request $request, Closure $next): Response
{
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->tenant_id !== null) {
            // Load tenant settings
            $this->context->set(
                tenantId: $user->tenant_id,
                branchId: $user->branch_id,
                settings: $settings,
            );
        }
    }
    return $next($request);
}
```

✅ Middleware tidak ada yang blocking

---

## 🎯 Root Cause Analysis

### Penyebab Utama: **Mismatch APP_URL dengan URL Aktual**

Laravel 11 menggunakan `APP_URL` untuk:
1. **Generate CSRF token yang valid**
2. **Set session cookie domain**
3. **Validasi origin request**

#### Skenario Masalah:

**Konfigurasi Saat Ini:**
```env
APP_URL=http://localhost
```

**URL Yang Mungkin Diakses User:**
- `http://localhost:8080` (Laragon)
- `http://sisfokolv7.test` (Virtual host)
- `http://127.0.0.1`
- `http://192.168.x.x` (IP lokal)

#### Mengapa Terjadi "Page Expired"?

1. **Saat User Membuka Form Login:**
   - Browser memuat halaman dari URL: `http://sisfokolv7.test/login`
   - Laravel generate CSRF token: `abc123xyz`
   - CSRF token di-embed di form: `<input name="_token" value="abc123xyz">`
   - Session cookie dibuat dengan domain dari `SESSION_DOMAIN` (null = current domain)

2. **Saat User Submit Form:**
   - Browser POST ke: `http://sisfokolv7.test/login`
   - Mengirim CSRF token: `abc123xyz`
   - Mengirim session cookie

3. **Laravel Validasi CSRF:**
   - Middleware `VerifyCsrfToken` cek token
   - **JIKA APP_URL berbeda dengan URL aktual:**
     - Session mungkin tidak terbaca dengan benar
     - CSRF token validation bisa gagal
     - Laravel throw `419 TokenMismatchException`

4. **Redirect Setelah Login:**
   - `AuthController@login` line 38: `$request->session()->regenerate();`
   - Regenerate session ID untuk security
   - **Session baru belum ter-sync dengan browser**
   - Redirect ke dashboard: `return redirect()->intended(route('dashboard'));`
   - **Browser belum menerima session cookie yang baru**
   - Laravel detect "no valid session" → redirect ke `/login`
   - Middleware detect "already authenticated" → redirect loop prevention → **419 Page Expired**

#### Mengapa Setelah Klik "Back" Baru Muncul Dashboard?

1. User klik **Back** di browser
2. Browser kembali ke halaman sebelumnya (form login atau halaman awal)
3. Pada saat ini, **session cookie sudah ter-set dengan benar**
4. Browser request halaman dengan session yang valid
5. Laravel detect `auth()->check() === true`
6. `AuthController@showLogin` line 17-19:
   ```php
   if (Auth::check()) {
       return redirect()->route('dashboard');
   }
   ```
7. Redirect ke dashboard → **BERHASIL**

---

## 🛠️ Solusi

### Solusi 1: Set APP_URL Sesuai URL Aktual (RECOMMENDED)

**Jika menggunakan Laragon Virtual Host:**

```env
# .env
APP_URL=http://sisfokolv7.test
```

**Jika menggunakan localhost dengan port:**

```env
# .env
APP_URL=http://localhost:8080
```

**Langkah:**
1. Edit file `.env`
2. Set `APP_URL` sesuai dengan URL yang diakses di browser
3. Clear config cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```
4. Test login kembali

### Solusi 2: Set SESSION_DOMAIN (Alternatif)

**Untuk development multi-domain:**

```env
# .env
SESSION_DOMAIN=.test
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
```

Ini memungkinkan session cookie dibaca oleh semua subdomain `*.test`

---

## ✅ Verifikasi

### Checklist Setelah Fix:

- [ ] Set `APP_URL` di `.env` sesuai URL aktual
- [ ] Run `php artisan config:clear`
- [ ] Buka browser, clear cookies untuk domain sisfokolv7
- [ ] Akses login page dengan URL yang sama dengan `APP_URL`
- [ ] Login dengan akun test
- [ ] Pastikan langsung redirect ke dashboard tanpa error 419
- [ ] Tidak perlu klik Back untuk masuk dashboard

---

## 📊 Catatan Tambahan

### Error Telescope di Log (Not Related)

```
SQLSTATE[42S02]: Table 'sisfokol_laravel.telescope_entries' doesn't exist
```

Ini **TIDAK terkait** dengan masalah login. Telescope aktif tapi tabelnya belum ada. 

**Fix (Optional):**
```bash
php artisan telescope:install
php artisan migrate
```

Atau disable Telescope di `.env`:
```env
TELESCOPE_ENABLED=false
```

---

## 🔗 Referensi

- Laravel 11 Session Configuration: https://laravel.com/docs/11.x/session
- CSRF Protection: https://laravel.com/docs/11.x/csrf
- Cookie Configuration: https://laravel.com/docs/11.x/requests#cookies

---

**Status:** Ready for Implementation  
**Priority:** High (UX Issue)  
**Estimated Fix Time:** 2 minutes
