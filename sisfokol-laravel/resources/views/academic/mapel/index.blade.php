@extends('layouts.app')

@section('title', 'Akademik — Mata Pelajaran')
@section('page-title', 'Manajemen Mata Pelajaran')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'controller' => \App\Modules\Academic\Controllers\MapelController::class,
        'columns' => [
            'kode' => 'Kode',
            'nama' => 'Nama',
            'jenis.nama' => 'Jenis',
            'kkm' => 'KKM',
            'jenjang' => 'Jenjang',
        ],
        'formFields' => [
            'kode' => ['label' => 'Kode', 'type' => 'text', 'placeholder' => 'Contoh: MTK'],
            'nama' => ['label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama mata pelajaran'],
            'mapel_jenis_id' => ['label' => 'Jenis Mapel', 'type' => 'select', 'options' => $jenisList->pluck('nama', 'id')->toArray()],
            'kkm' => ['label' => 'KKM', 'type' => 'number', 'placeholder' => '0 - 100'],
            'jenjang' => ['label' => 'Jenjang', 'type' => 'text', 'placeholder' => 'Contoh: SMP, SMA'],
        ],
    ])
</div>
@endsection
