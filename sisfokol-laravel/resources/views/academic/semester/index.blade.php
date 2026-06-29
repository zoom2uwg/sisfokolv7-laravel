@extends('layouts.app')

@section('title', 'Akademik — Semester')
@section('page-title', 'Semester')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'controller' => \App\Modules\Academic\Controllers\SemesterController::class,
        'columns' => [
            'tahunAjaran.nama' => 'Tahun Ajaran',
            'nama' => 'Semester',
            'tanggal_mulai' => 'Mulai',
            'tanggal_selesai' => 'Selesai',
            'aktif' => 'Status',
        ],
        'formFields' => [
            'tahun_ajaran_id' => ['label' => 'Tahun Ajaran', 'type' => 'select', 'options' => $tahunAjarans->pluck('nama', 'id')->toArray()],
            'nama' => ['label' => 'Semester', 'type' => 'select', 'options' => [1 => 'Semester 1', 2 => 'Semester 2']],
            'tanggal_mulai' => ['label' => 'Tanggal Mulai', 'type' => 'date'],
            'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'type' => 'date'],
            'aktif' => ['label' => 'Status', 'type' => 'checkbox', 'checkbox_label' => 'Aktif'],
        ],
    ])
</div>
@endsection
