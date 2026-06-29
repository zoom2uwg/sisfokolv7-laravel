@extends('layouts.app')

@section('title', 'Akademik — Kelas')
@section('page-title', 'Manajemen Kelas')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'controller' => \App\Modules\Academic\Controllers\KelasController::class,
        'columns' => [
            'nama' => 'Nama Kelas',
            'tingkat' => 'Tingkat',
            'waliKelas.nama' => 'Wali Kelas',
            'kapasitas' => 'Kapasitas',
            'branch.nama' => 'Cabang',
        ],
        'formFields' => [
            'nama' => ['label' => 'Nama Kelas', 'type' => 'text', 'placeholder' => 'Contoh: VII-A, X IPA 1'],
            'tingkat' => ['label' => 'Tingkat', 'type' => 'number', 'placeholder' => '1 - 12'],
            'kapasitas' => ['label' => 'Kapasitas', 'type' => 'number', 'placeholder' => 'Maks. siswa'],
            'wali_kelas_id' => ['label' => 'Wali Kelas', 'type' => 'select', 'options' => $gurus->pluck('nama', 'id')->toArray()],
            'branch_id' => ['label' => 'Cabang', 'type' => 'select', 'options' => $branches->pluck('nama', 'id')->toArray()],
        ],
    ])
</div>
@endsection
