@extends('layouts.app')

@section('title', 'Akademik — Kelas Siswa')
@section('page-title', 'Anggota Rombongan Belajar')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'controller' => \App\Modules\Academic\Controllers\KelasSiswaController::class,
        'columns' => [
            'siswa.nama' => 'Siswa',
            'kelas.nama' => 'Kelas',
            'tahunAjaran.nama' => 'Tahun Ajaran',
            'no_urut' => 'No. Urut',
        ],
        'formFields' => [
            'kelas_id' => ['label' => 'Kelas', 'type' => 'select', 'options' => $kelasList->pluck('nama', 'id')->toArray()],
            'siswa_id' => ['label' => 'Siswa', 'type' => 'select', 'options' => $siswaList->pluck('nama', 'id')->toArray()],
            'tahun_ajaran_id' => ['label' => 'Tahun Ajaran', 'type' => 'select', 'options' => $tahunAjaranList->pluck('nama', 'id')->toArray()],
            'no_urut' => ['label' => 'No. Urut', 'type' => 'number', 'placeholder' => 'Opsional'],
        ],
    ])
</div>
@endsection
