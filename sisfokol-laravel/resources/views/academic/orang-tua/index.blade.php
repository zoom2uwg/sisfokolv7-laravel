@extends('layouts.app')

@section('title', 'Akademik — Orang Tua / Wali')
@section('page-title', 'Orang Tua / Wali')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'controller' => \App\Modules\Academic\Controllers\OrangTuaController::class,
        'columns' => [
            'nama' => 'Nama',
            'hubungan' => 'Hubungan',
            'telepon' => 'Telepon',
            'email' => 'Email',
        ],
        'formFields' => [
            'nama' => ['label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama orang tua / wali'],
            'hubungan' => [
                'label' => 'Hubungan',
                'type' => 'select',
                'options' => ['ayah' => 'Ayah', 'ibu' => 'Ibu', 'wali' => 'Wali'],
            ],
            'telepon' => ['label' => 'Telepon', 'type' => 'text', 'placeholder' => 'No. telepon aktif'],
            'email' => ['label' => 'Email', 'type' => 'text', 'placeholder' => 'Alamat email'],
            'pekerjaan' => ['label' => 'Pekerjaan', 'type' => 'text'],
            'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'rows' => 2],
            'username' => ['label' => 'Username', 'type' => 'text', 'placeholder' => 'Username login (opsional)'],
            'password' => ['label' => 'Password', 'type' => 'text', 'placeholder' => 'Min. 6 karakter (kosongkan jika tidak diubah)'],
        ],
    ])
</div>
@endsection
