<?php

namespace Database\Seeders;

use App\Modules\Auth\Models\Field;
use Illuminate\Database\Seeder;

class FieldSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            ['kode' => 'siswa.nis',             'model' => 'Siswa',          'kolom' => 'nis',           'label' => 'NIS',          'kategori' => 'normal',           'default_visibility' => 'visible'],
            ['kode' => 'siswa.nama',            'model' => 'Siswa',          'kolom' => 'nama',          'label' => 'Nama',         'kategori' => 'normal',           'default_visibility' => 'visible'],
            ['kode' => 'siswa.telepon',         'model' => 'Siswa',          'kolom' => 'telepon',       'label' => 'Telepon',      'kategori' => 'sensitif',         'default_visibility' => 'hidden'],
            ['kode' => 'siswa.alamat',          'model' => 'Siswa',          'kolom' => 'alamat',        'label' => 'Alamat',       'kategori' => 'sensitif',         'default_visibility' => 'hidden'],
            ['kode' => 'siswa.tanggal_lahir',   'model' => 'Siswa',          'kolom' => 'tanggal_lahir', 'label' => 'Tanggal Lahir','kategori' => 'sensitif',         'default_visibility' => 'hidden'],
            ['kode' => 'orang_tua.telepon',     'model' => 'OrangTua',       'kolom' => 'telepon',       'label' => 'Telepon Ortu', 'kategori' => 'sangat_sensitif',  'default_visibility' => 'hidden'],
            ['kode' => 'orang_tua.email',       'model' => 'OrangTua',       'kolom' => 'email',         'label' => 'Email Ortu',   'kategori' => 'sangat_sensitif',  'default_visibility' => 'hidden'],
            ['kode' => 'tagihan.nominal_kurang','model' => 'TagihanSiswa',  'kolom' => 'nominal_kurang','label' => 'Tunggakan',    'kategori' => 'sangat_sensitif',  'default_visibility' => 'hidden'],
            ['kode' => 'pembayaran.total',      'model' => 'Pembayaran',     'kolom' => 'total',         'label' => 'Total Bayar',  'kategori' => 'sangat_sensitif',  'default_visibility' => 'hidden'],
            ['kode' => 'tabungan.saldo',        'model' => 'TabunganSiswa', 'kolom' => 'saldo',         'label' => 'Saldo',        'kategori' => 'sangat_sensitif',  'default_visibility' => 'hidden'],
        ];

        foreach ($fields as $f) {
            Field::firstOrCreate(['kode' => $f['kode']], $f);
        }
    }
}
