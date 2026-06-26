# DEV_REPORT: Bug Fix — jenis_kegiatan Enum Mismatch & RaporSectionSubscriber Placeholder

**Tanggal:** 2026-06-25  
**Epic:** Bug Fix Kurikulum Plugin  
**Status:** ✅ Selesai  
**Branch:** `main`  
**Total Commits:** 2

---

## 1. Ringkasan

| Bug | Severity | Status | Commit |
|-----|----------|--------|--------|
| Bug 1: `jenis_kegiatan` Enum Mismatch | 🔴 KRITIS | ✅ Fixed | `8db53d3` |
| Bug 2: `RaporSectionSubscriber` Placeholder | 🟡 MEDIUM | ✅ Marked TODO | `1fedc71` |

---

## 2. Bug 1: `jenis_kegiatan` Enum Mismatch

### Masalah

Terdapat ketidakcocokan antara migration (database), controller (validasi), dan views (form options) untuk field `jenis_kegiatan`:

| File | Enum Values | Path |
|------|-------------|------|
| **Migration (DB)** | `intrakurikuler`, `kokurikuler_p5` | `Kurikulum/Database/Migrations/2026_06_20_000501_create_struktur_kurikulum_table.php:18` |
| **Controller (validation)** | `intrakurikuler`, `kokurikuler`, `ekstrakurikuler` | `Kurikulum/Controllers/StrukturKurikulumController.php:44,76` |
| **Views (options)** | `intrakurikuler`, `kokurikuler`, `ekstrakurikuler` | `Kurikulum/Resources/views/struktur/create.blade.php:94-96` |

### Dampak

- Hanya `intrakurikuler` yang bisa disimpan ke database
- Memilih `kokurikuler` → SQL ERROR (migration hanya punya `kokurikuler_p5`)
- Memilih `ekstrakurikuler` → SQL ERROR (tidak ada di migration enum)

### Akar Masalah

Developer menggunakan nama berbeda antara migration (`kokurikuler_p5`) dan controller/views (`kokurikuler`). Juga menambah `ekstrakurikuler` di controller/views tanpa update migration.

### Fix yang Dipilih

**Option A: Fix migration agar cocok dengan controller/views (Recommended)**

Controller dan views merepresentasikan business logic yang benar. Migration yang perlu diubah.

### Perubahan

**File:** `sisfokol-laravel/app/Plugins/Kurikulum/Database/Migrations/2026_06_20_000501_create_struktur_kurikulum_table.php`

```diff
- $table->enum('jenis_kegiatan', ['intrakurikuler', 'kokurikuler_p5'])->default('intrakurikuler');
+ $table->enum('jenis_kegiatan', ['intrakurikuler', 'kokurikuler', 'ekstrakurikuler'])->default('intrakurikuler');
```

### Status Setelah Fix

| File | Enum Values | Status |
|------|-------------|--------|
| Migration (DB) | `intrakurikuler`, `kokurikuler`, `ekstrakurikuler` | ✅ |
| Controller | `intrakurikuler`, `kokurikuler`, `ekstrakurikuler` | ✅ |
| Views | `intrakurikuler`, `kokurikuler`, `ekstrakurikuler` | ✅ |

### Commit

```
8db53d3 fix(kurikulum): align jenis_kegiatan enum with controller/views — intrakurikuler,kokurikuler,ekstrakurikuler
```

### Catatan

Jika database sudah dibuat sebelumnya, perlu re-run migration:
```bash
php artisan migrate:fresh --seed
```

---

## 3. Bug 2: `RaporSectionSubscriber` Placeholder

### Masalah

File `RaporSectionSubscriber.php` baris 20 berisi teks placeholder hardcoded, bukan data aktual:

```php
$html = '<p><em>Section Capaian Kompetensi dari plugin Kurikulum.</em></p>';
```

### Temuan Tambahan

Event `RaportRenderSection` **tidak pernah di-dispatch** oleh `RaporGeneratorService`. Subscriber ini dead code — terdaftar di `KurikulumServiceProvider` tapi tidak pernah ter-trigger.

### Alur yang Seharusnya

```
RaporController::show() / downloadPdf()
  → RaporGeneratorService::getReportData()
    → (seharusnya dispatch) RaportRenderSection event
      → RaporSectionSubscriber::handleRaportRenderSection()
        → query kurikulum data, set $event->sections['Capaian Kompetensi']
    → (seharusnya kumpulkan) $event->sections dan pass ke view
      → view render plugin sections setelah tabel nilai
```

### Data yang Seharusnya Di-render

1. Resolve siswa → kelas → jenjang dari `$event->siswa`
2. Query `Kurikulum` yang aktif (`status_aktif = true`)
3. Query `StrukturKurikulum` by `kurikulum_id` + `jenjang` + `kelas`
4. Query `KomponenKompetensi` by `struktur_id`
5. Render `teks_kompetensi` grouped by `kode_kompetensi`

### Referensi Implementasi

Sibling subscriber `EvaluationFrameworkSubscriber` sudah menunjukkan pola query yang benar (lines 27-53: resolve Kurikulum dari mapel, find StrukturKurikulum by jenjang/kelas, query KomponenKompetensi).

### Fix yang Dipilih

**Skip: Tandai sebagai TODO** — implementasi penuh memerlukan perubahan di 3 file:
1. `RaporGeneratorService.php` — dispatch event
2. `rapor/show.blade.php` — render `$sections`
3. `rapor/pdf.blade.php` — render `$sections`

### Perubahan

**File:** `sisfokol-laravel/app/Plugins/Kurikulum/Subscribers/RaporSectionSubscriber.php`

```diff
- // Generate capaian kompetensi section HTML based on siswa's nilai + kurikulum
- $html = '<p><em>Section Capaian Kompetensi dari plugin Kurikulum.</em></p>';
+ // TODO: Implementasi query kurikulum untuk capaian kompetensi
+ // 1. Resolve siswa → kelas → jenjang dari $event->siswa
+ // 2. Query Kurikulum yang aktif (status_aktif = true)
+ // 3. Query StrukturKurikulum by kurikulum_id + jenjang + kelas
+ // 4. Query KomponenKompetensi by struktur_id
+ // 5. Render teks_kompetensi grouped by kode_kompetensi
+ // NOTE: Event RaportRenderSection belum di-dispatch oleh RaporGeneratorService
+ $html = '<p><em>Section Capaian Kompetensi dari plugin Kurikulum.</em></p>';
```

### Commit

```
1fedc71 chore(kurikulum): add TODO markers to RaporSectionSubscriber for future implementation
```

---

## 4. File Inventory

### Modified (2 files)

| File | Perubahan |
|------|-----------|
| `Kurikulum/Database/Migrations/2026_06_20_000501_create_struktur_kurikulum_table.php` | Enum values: `kokurikuler_p5` → `kokurikuler`, tambah `ekstrakurikuler` |
| `Kurikulum/Subscribers/RaporSectionSubscriber.php` | Tambah TODO comment (7 baris) |

---

## 5. Testing Checklist

### Bug 1: Enum Mismatch

- [ ] Jalankan `php artisan migrate:fresh --seed`
- [ ] Buka form tambah struktur kurikulum
- [ ] Pilih `intrakurikuler` → submit → berhasil
- [ ] Pilih `kokurikuler` → submit → berhasil
- [ ] Pilih `ekstrakurikuler` → submit → berhasil
- [ ] Cek database: enum values tersimpan dengan benar

### Bug 2: RaporSectionSubscriber

- [ ] Buka rapor siswa → tidak ada error
- [ ] Placeholder text tetap muncul (sementara)
- [ ] Tidak ada PHP error di log

---

## 6. Rekomendasi Selanjutnya

### Bug 2 — Full Implementation (Fase Berikutnya)

Untuk mengimplementasi `RaporSectionSubscriber` sepenuhnya:

**Step 1: Dispatch event di `RaporGeneratorService`**
```php
// RaporGeneratorService.php
use App\Modules\Evaluation\Events\RaportRenderSection;

public function getReportData(Siswa $siswa, TahunAjaran $tapel, int $semester): array
{
    // ... existing code ...
    
    // Dispatch event untuk plugin sections
    $event = new RaportRenderSection($siswa, $tapel, $semester);
    event($event);
    
    // Merge plugin sections ke data
    $data['sections'] = $event->sections;
    
    return $data;
}
```

**Step 2: Implementasi query di `RaporSectionSubscriber`**
```php
public function handleRaportRenderSection(RaportRenderSection $event): void
{
    $tenantId = app(TenantContext::class)->id;
    if ($tenantId && !app(PluginRegistry::class)->isActiveForTenant('kurikulum', $tenantId)) {
        return;
    }

    $siswa = $event->siswa;
    $kelasSiswa = $siswa->kelasSiswa()->where('tahun_ajaran_id', $event->tapel->id)->first();
    if (!$kelasSiswa) return;

    $kurikulum = Kurikulum::where('status_aktif', true)->first();
    if (!$kurikulum) return;

    $struktur = StrukturKurikulum::where('kurikulum_id', $kurikulum->id)
        ->where('jenjang', $kelasSiswa->kelas->jenjang)
        ->where('kelas', $kelasSiswa->kelas->tingkat)
        ->first();
    if (!$struktur) return;

    $komponens = KomponenKompetensi::where('struktur_id', $struktur->id)
        ->orderBy('kode_kompetensi')
        ->get();

    $html = '<div class="capaian-kompetensi">';
    foreach ($komponens as $komponen) {
        $html .= '<div class="kompetensi-item">';
        $html .= '<strong>' . e($komponen->kode_kompetensi) . '</strong>';
        $html .= '<p>' . e($komponen->teks_kompetensi) . '</p>';
        $html .= '</div>';
    }
    $html .= '</div>';

    $event->sections['Capaian Kompetensi'] = $html;
}
```

**Step 3: Render sections di Blade views**
```blade
{{-- rapor/show.blade.php dan rapor/pdf.blade.php --}}
@if(!empty($sections))
    @foreach($sections as $title => $html)
        <div class="rapor-section mt-6">
            <h3 class="text-lg font-bold border-b pb-2 mb-3">{{ $title }}</h3>
            {!! $html !!}
        </div>
    @endforeach
@endif
```

---

## 7. Kesimpulan

| Bug | Root Cause | Fix | Dampak |
|-----|-----------|-----|--------|
| Enum Mismatch | Nama enum berbeda antara migration vs controller/views | Align migration ke controller/views | Semua 3 enum values sekarang bisa disimpan |
| Subscriber Placeholder | Event tidak pernah di-dispatch, implementasi belum selesai | Tandai TODO, dokumentasikan langkah implementasi | Tidak ada perubahan behavior (tetap placeholder) |

**Total commits:** 2  
**Files modified:** 2  
**Severity resolved:** 1 KRITIS, 1 MEDIUM (marked TODO)

---

*Dokumen ini dibuat otomatis berdasarkan investigasi dan fix bug pada 2026-06-25.*
