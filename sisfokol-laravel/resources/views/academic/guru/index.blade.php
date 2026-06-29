@extends('layouts.app')

@section('title', 'Akademik — Guru')
@section('page-title', 'Manajemen Guru')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'controller' => \App\Modules\Academic\Controllers\GuruController::class,
        'columns' => [
            'nip' => 'NIP',
            'nama' => 'Nama',
            'jenis_kelamin' => 'L/P',
            'email' => 'Email',
            'jabatan' => 'Jabatan',
            'aktif' => 'Status',
        ],
        'formFields' => [
            'nip' => ['label' => 'NIP', 'type' => 'text', 'placeholder' => 'Nomor Induk Pegawai'],
            'nama' => ['label' => 'Nama Lengkap', 'type' => 'text'],
            'jenis_kelamin' => ['label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
            'telepon' => ['label' => 'Telepon', 'type' => 'text'],
            'email' => ['label' => 'Email', 'type' => 'text'],
            'jabatan' => ['label' => 'Jabatan', 'type' => 'text', 'placeholder' => 'Contoh: Guru Matematika'],
            'aktif' => ['label' => 'Status', 'type' => 'checkbox', 'checkbox_label' => 'Aktif'],
        ],
    ])
</div>
@endsection
