@extends('layouts.app')

@section('title', 'Akademik — Detail Siswa')
@section('page-title', 'Detail Profil Siswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">Profil Siswa</h1>
            <p class="text-sm text-slate-400 mt-1">Detail informasi akademik dan profil siswa.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('academic.siswa.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-350 hover:text-slate-100 rounded-xl text-sm font-medium transition border border-slate-700">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            @can('update', $siswa)
                <a href="{{ route('academic.siswa.edit', $siswa) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-medium transition shadow-md shadow-indigo-600/20">
                    <i class="fas fa-edit"></i> Edit Profil
                </a>
            @endcan
        </div>
    </div>

    <!-- Detail Info Card -->
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl overflow-hidden shadow-xl">
        <!-- Top Cover Accent -->
        <div class="h-24 bg-gradient-to-r from-indigo-950 via-slate-900 to-purple-950/60 border-b border-slate-800/50 flex items-end p-6">
            <div class="flex items-center gap-4 translate-y-12">
                <div class="h-20 w-20 rounded-2xl bg-indigo-650 flex items-center justify-center text-white font-bold text-3xl border-4 border-slate-900 shadow-xl">
                    {{ substr($siswa->nama, 0, 1) }}
                </div>
                <div class="pb-2">
                    <h2 class="text-lg font-bold text-slate-100">{{ $siswa->nama }}</h2>
                    <p class="text-xs text-slate-455 font-medium">NIS: {{ $siswa->nis }}</p>
                </div>
            </div>
        </div>

        <div class="p-6 pt-16 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- NISN -->
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">NISN</span>
                    <span class="text-sm font-medium text-slate-200 mt-1 block">{{ $siswa->nisn ?? '-' }}</span>
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Kelamin</span>
                    <span class="text-sm font-medium text-slate-200 mt-1 block">
                        {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} ({{ $siswa->jenis_kelamin }})
                    </span>
                </div>

                <!-- Tempat, Tanggal Lahir (Field ACL) -->
                @field('siswa.tanggal_lahir')
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Tempat, Tanggal Lahir</span>
                    <span class="text-sm font-medium text-slate-200 mt-1 block">
                        {{ $siswa->tempat_lahir ?? '-' }}, {{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>
                @endfield

                <!-- Agama -->
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Agama</span>
                    <span class="text-sm font-medium text-slate-200 mt-1 block">{{ $siswa->agama ?? '-' }}</span>
                </div>

                <!-- Telepon (Field ACL) -->
                @field('siswa.telepon')
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Nomor Telepon</span>
                        <span class="text-sm font-medium text-slate-200 mt-1 block">{{ $siswa->telepon ?? '-' }}</span>
                    </div>
                @endfield

                <!-- Status -->
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Keaktifan</span>
                    <div class="mt-1">
                        @if($siswa->status === 'aktif')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-950/40 text-emerald-450 border border-emerald-900/50">Aktif</span>
                        @elseif($siswa->status === 'nonaktif')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-rose-950/40 text-rose-450 border border-rose-900/50">Nonaktif</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-800 text-slate-450 border border-slate-700/60">{{ ucfirst($siswa->status) }}</span>
                        @endif
                    </div>
                </div>

                <!-- Alamat (Field ACL) -->
                @field('siswa.alamat')
                <div class="sm:col-span-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Alamat Lengkap</span>
                    <span class="text-sm font-medium text-slate-200 mt-1 block">{{ $siswa->alamat ?? '-' }}</span>
                </div>
                @endfield
            </div>
        </div>
    </div>
</div>
@endsection
