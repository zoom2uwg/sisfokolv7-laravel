@extends('layouts.app')

@section('title', 'Akademik — Jenis Mapel')
@section('page-title', 'Jenis Mata Pelajaran')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'controller' => \App\Modules\Academic\Controllers\MapelJenisController::class,
        'columns' => [
            'kode' => 'Kode',
            'nama' => 'Nama',
        ],
        'formFields' => [
            'kode' => ['label' => 'Kode', 'type' => 'text', 'placeholder' => 'Contoh: Wajib, Pilihan'],
            'nama' => ['label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama jenis mata pelajaran'],
        ],
    ])
</div>
@endsection
