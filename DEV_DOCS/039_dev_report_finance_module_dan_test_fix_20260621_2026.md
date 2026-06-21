# DEV_DOCS-039: Dev Report — Penyelesaian Modul Keuangan (Epic 7) & Perbaikan Test Suite Rapor

- **Tanggal:** 2026-06-21 20:26
- **Status:** ✅ SELESAI
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** SISFOKOL v7 Laravel — Modular Monolith

---

## 🎯 PENYELESAIAN EPIC 7: FINANCE MODULE

Seluruh komponen pada **Epic 7: Finance Module (Keuangan & Tabungan Siswa)** telah berhasil diimplementasikan dan diintegrasikan:

1. **Migrations & Models**:
   * Mengimplementasikan 5 tabel keuangan baru dengan isolasi penyewa (`BelongsToTenant`) dan pencatatan audit (`TracksAuditColumns`): `item_pembayaran`, `tagihan_siswa`, `pembayaran`, `pembayaran_rincian`, dan `tabungan_siswa`.
2. **Core Services**:
   * `PembayaranService`: Menggunakan transaksi database atomik dan **pessimistic locking (`lockForUpdate()`)** untuk mengamankan data tagihan dari race condition saat pembayaran ganda konkuren.
   * `KwitansiGenerator`: Menghasilkan nomor nota unik secara sekuensial harian per penyewa (`INV-YYYYMMDD-XXXX`).
   * `TagihanGeneratorService` & Command: Generator tagihan bulanan terjadwal yang bersifat idempotent.
   * `TabunganMutasiService`: Mengamankan proses simpanan (setor) dan penarikan (tarik) dengan limitasi saldo yang ketat.
3. **UI/UX Kasir & Riwayat**:
   * Merancang antarmuka kasir pembayaran interaktif menggunakan Tailwind CSS + Alpine.js untuk menghitung nominal kembalian dan cetak kwitansi PDF via DomPDF secara instan.
   * Merancang halaman detail mutasi tabungan siswa.

---

## 🛠️ INVESTIGASI & PERBAIKAN TEST SUITE (RAPOR GENERATOR TEST)

Saat melakukan pengujian penuh, ditemukan kegagalan pengujian pada unit test Rapor:
`Tests\Feature\Evaluation\RaporGeneratorTest > it aggregates rapor data correctly` berstatus **FAILED** (menghasilkan 0 ketidakhadiran dari yang seharusnya 2).

### Akar Masalah (Root Cause)
1. Model `Student` memiliki proteksi mass assignment (`$fillable`) di mana kolom primary key `id` **tidak dimasukkan** ke dalam properti `$fillable`.
2. Pada berkas pengujian `RaporGeneratorTest.php`, instansiasi dilakukan dengan cara:
   ```php
   $this->student = Student::create([
       'id' => $this->siswa->id,
       ...
   ]);
   ```
   Karena `id` tidak fillable, Eloquent mengabaikan parameter `'id'` tersebut dan membiarkan database MySQL menetapkan nilai auto-increment berikutnya untuk tabel `students`.
3. Dalam MySQL, rollback transaksi (`RefreshDatabase`) **tidak mereset nilai auto-increment**. Akibatnya, nilai auto-increment antara tabel `siswa` dan tabel `students` mengalami ketidakselarasan (*divergence*) saat dijalankan bersamaan dengan pengujian lain di dalam suite (di mana pengujian lain memasukkan data siswa tetapi tidak memasukkan data student).
4. Sehingga, `$this->siswa->id` bernilai berbeda dengan `$this->student->id`, menyebabkan kueri presensi (`attendable_id`) tidak cocok dan mengembalikan hasil 0.

### Solusi / Perbaikan
Mengubah cara instansiasi model `Student` pada pengujian agar menetapkan ID secara eksplisit (melewati mass-assignment protection) sebelum disimpan:
```php
$this->student = new Student([
    'academic_year_id' => $this->academicYear->id,
    'classroom_id' => $this->classroom->id,
    'nis' => '2026002',
    'name' => 'Siti Aminah',
    'gender' => 'P',
    'is_active' => true,
]);
$this->student->id = $this->siswa->id; // Penugasan eksplisit melewati $fillable
$this->student->save();
```

Setelah perbaikan di atas diterapkan, pengujian `RaporGeneratorTest` berjalan dengan sukses baik secara terisolasi maupun sebagai bagian dari test suite penuh.

---

## 📊 HASIL PENGUJIAN AKHIR (TEST SUITE RESULTS)

Hasil eksekusi penuh test suite aplikasi (`php83 artisan test`) setelah perbaikan:

```
  Tests:    112 passed (279 assertions)
  Duration: 155.03s
```

Seluruh 112 pengujian berstatus **PASS** (100% hijau) ✅.
