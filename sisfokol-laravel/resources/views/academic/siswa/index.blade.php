@extends('layouts.app')

@section('title', 'Akademik — Siswa')
@section('page-title', 'Manajemen Siswa')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'controller' => \App\Modules\Academic\Controllers\SiswaController::class,
        'columns' => [
            'nis' => 'NIS',
            'nama' => 'Nama',
            'jenis_kelamin' => 'L/P',
            'status' => 'Status',
        ],
        'formFields' => [
            'nis' => ['label' => 'NIS', 'type' => 'text', 'placeholder' => 'Nomor Induk Siswa'],
            'nisn' => ['label' => 'NISN', 'type' => 'text', 'placeholder' => 'Nomor Induk Siswa Nasional'],
            'nama' => ['label' => 'Nama Lengkap', 'type' => 'text'],
            'jenis_kelamin' => ['label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
            'tempat_lahir' => ['label' => 'Tempat Lahir', 'type' => 'text'],
            'tanggal_lahir' => ['label' => 'Tanggal Lahir', 'type' => 'date'],
            'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'rows' => 2],
            'telepon' => ['label' => 'Telepon', 'type' => 'text'],
            'agama' => ['label' => 'Agama', 'type' => 'text', 'placeholder' => 'Contoh: Islam, Kristen, dll.'],
            'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah', 'keluar' => 'Keluar']],
        ],
    ])
</div>
@endsection
