# Analisis Keamanan Crudlfix — Tenant Isolation & RBAC

**Dibuat:** 2026-06-25
**Author:** ZCode
**Status:** ✅ FIXED

---

## Ringkasan Eksekutif

Crudlfix trait ditemukan memiliki **2 kerentanan keamanan kritis** terkait multi-tenant dan RBAC. Kedua masalah telah diperbaiki.

| No | Masalah | Severity | Status |
|----|---------|----------|--------|
| 1 | `resolveModel()` bypass tenant global scope | 🔴 KRITIS | ✅ Fixed |
| 2 | Tidak set team context untuk Spatie Permission | 🟠 TINGGI | ✅ Fixed |

---

## Detail Masalah

### 1. `resolveModel()` Bypass Tenant Scope (🔴 KRITIS)

#### Sebelum Fix:

```php
protected function resolveModel(string $param): Model
{
    $cfg = $this->config();
    $id = request()->route($param);
    return $cfg->model::withoutGlobalScopes()->findOrFail($id);  // ❌ DANGER!
}
```

#### Masalah:

- `withoutGlobalScopes()` **menghapus SEMUA global scope**, termasuk `tenant` scope
- User dari Tenant A bisa akses data Tenant B dengan mengubah ID di URL
- Contoh serangan:
  - User Tenant A akses: `/academic/siswa/50` (ID 50 milik Tenant A) → ✅ OK
  - User Tenant A akses: `/academic/siswa/105` (ID 105 milik Tenant B) → ❌ SEHARUSNYA 404, TAPI BERHASIL AKSES

#### Dampak:

- **Data leakage** antar tenant
- **Unauthorized access** ke data sensitif (nilai siswa, keuangan, dll)
- Melanggar ADR-003 (Multi-tenant SaaS Shared DB)

#### Sesudah Fix:

```php
protected function resolveModel(string $param): Model
{
    $cfg = $this->config();
    $id = request()->route($param);

    // Check if model uses BelongsToTenant trait
    $usesTenantTrait = in_array(
        \App\Models\Traits\BelongsToTenant::class,
        class_uses_recursive($cfg->model)
    );

    if ($usesTenantTrait) {
        // Find within tenant scope (global scope applies)
        $model = $cfg->model::find($id);

        if (!$model) {
            // Model not found OR belongs to different tenant
            // Return 404 to avoid data leakage (don't reveal existence)
            abort(404, 'Data tidak ditemukan.');
        }

        return $model;
    }

    // Model doesn't use BelongsToTenant (e.g., Tenant itself, global models)
    return $cfg->model::findOrFail($id);
}
```

#### Perbaikan:

- ✅ Deteksi apakah model menggunakan `BelongsToTenant` trait
- ✅ Jika ya, gunakan `find()` dengan global scope tetap aktif
- ✅ Return 404 jika data tidak ditemukan (termasuk jika milik tenant lain)
- ✅ Tidak ada data leakage (tidak mengungkap keberadaan data tenant lain)

---

### 2. Tidak Set Team Context untuk Spatie Permission (🟠 TINGGI)

#### Sebelum Fix:

```php
// Permission mode: Spatie permission check
$permission = "{$cfg->authorize}.{$action}";
$user = auth()->user();

if (!$user) {
    abort(403, 'Tidak memiliki akses.');
}

// Don't set team context — permissions are global, let Spatie resolve
if (!$user->can($permission)) {
    abort(403, 'Tidak memiliki akses.');
}
```

#### Masalah:

- Spatie Permission dengan `teams` mode memerlukan `setPermissionsTeamId($team_id)`
- Tanpa set team, permission check menggunakan team_id dari user saat ini
- Jika user tidak punya team_id, permission check bisa tidak akurat
- Lihat ADR-006 untuk detail arsitektur RBAC

#### Sesudah Fix:

```php
// Permission mode: Spatie permission check
$permission = "{$cfg->authorize}.{$action}";
$user = auth()->user();

if (!$user) {
    abort(403, 'Tidak memiliki akses.');
}

// ADR-006: Set team context for Spatie Permission teams mode
$tenantCtx = app(TenantContext::class);
if ($tenantCtx->isInitialized()) {
    // Set team_id for permission check
    setPermissionsTeamId($tenantCtx->id);
}

if (!$user->can($permission)) {
    abort(403, 'Tidak memiliki akses.');
}
```

#### Perbaikan:

- ✅ Set `team_id` dari `TenantContext` sebelum permission check
- ✅ Hanya set jika tenant context sudah diinisialisasi (bukan superadmin)
- ✅ Memastikan permission check sesuai dengan tenant yang sedang aktif

---

## Analisis Komponen Lainnya

### ✅ `index()` — AMAN

```php
public function index(Request $request): View
{
    $query = $cfg->model::query();  // Global scope tenant aktif
    // ...
}
```

- Menggunakan `query()` tanpa bypass global scope
- `BelongsToTenant` trait otomatis menambahkan `WHERE tenant_id = ?`

### ✅ `store()` — AMAN

```php
public function store(Request $request): RedirectResponse
{
    $model = $cfg->model::create($validated);  // Auto-fill tenant_id dari trait
    // ...
}
```

- `BelongsToTenant::bootBelongsToTenant()` mengisi `tenant_id` otomatis saat `creating`
- Tidak perlu manual set `tenant_id`

### ✅ `export()` — AMAN

```php
public function export(Request $request)
{
    $query = $cfg->model::query();  // Global scope tenant aktif
    // ...
}
```

- Export hanya data milik tenant yang sedang aktif

---

## Testing Checklist

Setelah fix, verifikasi dengan test berikut:

### Test 1: Tenant Isolation di `resolveModel()`

```php
// Setup: 2 tenant, masing-masing punya siswa
$tenantA = Tenant::factory()->create();
$tenantB = Tenant::factory()->create();
$siswaA = Siswa::factory()->forTenant($tenantA)->create();
$siswaB = Siswa::factory()->forTenant($tenantB)->create();

// Test: User Tenant A akses Siswa Tenant B
$this->actingAs($userTenantA);
tenantContext()->initialize($tenantA);

$response = $this->get("/academic/siswa/{$siswaB->id}");
$response->assertStatus(404);  // Harus 404, bukan 200
```

### Test 2: Team Context di Permission Check

```php
// Setup: User dengan permission di tenant tertentu
$user = User::factory()->create();
$tenant = Tenant::factory()->create();
$user->assignRole('admin');  // Role dengan permission

// Test: Permission check dengan team context
tenantContext()->initialize($tenant);
$this->actingAs($user);

$response = $this->get('/academic/siswa');
$response->assertStatus(200);  // Harus bisa akses
```

---

## Referensi

| Dokumen | Keterangan |
|---------|------------|
| ADR-003 | Multi-tenant SaaS Shared DB |
| ADR-006 | Granular Database-driven RBAC |
| ADR-010 | RBAC Menu dan Field Level ACL |
| `BelongsToTenant.php` | Trait untuk tenant isolation |
| `TenantContext.php` | Service untuk menyimpan tenant aktif |

---

## Changelog

| Tanggal | Perubahan |
|---------|-----------|
| 2026-06-25 | Initial analysis — 2 masalah kritis ditemukan |
| 2026-06-25 | Fix `resolveModel()` untuk tenant isolation |
| 2026-06-25 | Fix `authorizeCrudlfix()` untuk team context |
| 2026-06-25 | Backup lama disimpan di `backups/php/Support/Crudlfix/Crudlfix.php.bak_20260625` |
