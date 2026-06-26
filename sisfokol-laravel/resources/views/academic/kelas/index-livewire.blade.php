@extends('layouts.app')

@section('title', 'Akademik — Kelas')
@section('page-title', 'Manajemen Kelas')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'model' => \App\Modules\Academic\Models\Kelas::class,
        'view' => 'academic.kelas',
        'route' => 'academic.kelas',
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
        'search' => ['nama'],
        'with' => ['waliKelas', 'branch'],
        'rules' => [
            'nama' => 'required|string|max:30',
            'tingkat' => 'required|integer|min:1|max:12',
            'kapasitas' => 'nullable|integer|min:1|max:100',
            'wali_kelas_id' => 'nullable|exists:guru,id',
            'branch_id' => 'nullable|exists:branches,id',
        ],
        'viewData' => [
            'gurus' => $gurus,
            'branches' => $branches,
        ],
        'perPage' => 20,
        'authorize' => 'kelas',
        'authType' => 'permission',
    ])
</div>
@endsection
