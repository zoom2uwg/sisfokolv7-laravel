# Rencana Implementasi Peningkatan UI/UX Livewire Crudlfix

Rencana ini bertujuan untuk meningkatkan kenyamanan pengguna (UI/UX) pada perpustakaan Crudlfix menggunakan perbaikan prioritas tinggi (High) dan menengah (Medium) yang telah disepakati.

## User Review Required

> [!IMPORTANT]
> - Seluruh perubahan dilakukan pada file view bersama (shared views) dari perpustakaan Crudlfix.
> - Perubahan ini akan otomatis berdampak ke seluruh modul CRUD yang menggunakan sistem Crudlfix (seperti Kelas, Siswa, Guru, Mapel, dll.).
> - Kami menggunakan Alpine.js terintegrasi `@entangle` untuk dropdown pencarian, menghindari ketergantungan library JS eksternal baru.

---

## Proposed Changes

### [Component: Crudlfix Form]

Peningkatan untuk mengatasi premature validation, kegunaan select dropdown data besar, dan dokumentasi field input.

#### [MODIFY] [form.blade.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/resources/views/livewire/crudlfix/form.blade.php)

- Mengubah tipe data binding `wire:model.live` menjadi `wire:model.blur` pada elemen `textarea`, `date`, dan input teks default untuk menunda validasi hingga input kehilangan fokus (blur).
- Menggantikan elemen `<select>` bawaan browser dengan Searchable Dropdown berbasis Alpine.js yang terhubung ke Livewire melalui `@entangle`.
- Menambahkan rendering deskripsi pembantu (`help`) di bawah label input.

---

### [Component: Crudlfix Table]

Peningkatan respon visual proses data (loading states) dan responsivitas tabel pada layar mobile.

#### [MODIFY] [table.blade.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/resources/views/livewire/crudlfix/table.blade.php)

- Menambahkan class `relative` pada pembungkus tabel.
- Menyisipkan overlay loading state modern menggunakan `wire:loading.delay.longest` sehingga transisi pencarian/sorting/halaman terasa responsif secara visual tanpa flicker pada koneksi cepat.
- Menambahkan logika parsing array pada kolom `$columns` untuk mendukung class breakpoint responsive (misalnya `'class' => 'hidden md:table-cell'`).

---

## Verification Plan

### Automated Tests
- Menjalankan seluruh pengujian unit/feature di dalam modul Finance dan Academic untuk memastikan fungsionalitas CRUD dasar tidak terganggu.
```bash
php83 artisan test
```

### Manual Verification
- Melakukan verifikasi visual di browser pada salah satu halaman manajemen (misalnya Manajemen Siswa atau Mata Pelajaran).
- Menguji alur:
  1. Mengetik di form input: memastikan pesan validasi tidak langsung melompat sebelum berpindah input.
  2. Memilih dropdown: memastikan filter pencarian di combobox berfungsi instan secara lokal.
  3. Mengetik di kolom pencarian tabel: memastikan indikator loading muncul saat pemrosesan data lambat.
  4. Mengubah ukuran layar ke mobile: memastikan kolom dengan class breakpoint tersembunyi dengan benar.
