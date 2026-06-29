@extends('layouts.app')

@section('title', 'Akademik — Tahun Ajaran')
@section('page-title', 'Tahun Ajaran')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'controller' => \App\Modules\Academic\Controllers\TahunAjaranController::class,
        'columns' => [
            'nama' => 'Tahun Ajaran',
            'tanggal_mulai' => 'Mulai',
            'tanggal_selesai' => 'Selesai',
            'aktif' => 'Status',
        ],
        'formFields' => [
            'nama' => ['label' => 'Nama', 'type' => 'text', 'placeholder' => 'Contoh: 2026/2027'],
            'tanggal_mulai' => ['label' => 'Tanggal Mulai', 'type' => 'date'],
            'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'type' => 'date'],
            'aktif' => ['label' => 'Status', 'type' => 'checkbox', 'checkbox_label' => 'Aktif'],
        ],
    ])
</div>
@endsection
