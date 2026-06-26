@extends('layouts.app')

@section('title', 'Akademik — Detail Mata Pelajaran')
@section('page-title', 'Detail Mata Pelajaran')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">{{ $mapel->nama }}</h1>
            <p class="text-sm text-slate-400 mt-1">Informasi detail mata pelajaran.</p>
        </div>
        <a href="{{ route('academic.mapel.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-slate-100 rounded-xl text-sm font-medium transition border border-slate-700">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Info Card -->
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl p-6 shadow-xl space-y-6">
        <!-- Kode & Jenis -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kode Mata Pelajaran</p>
                <p class="text-sm text-slate-200 bg-slate-950/30 px-3 py-2 rounded-lg">{{ $mapel->kode }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Jenis Mata Pelajaran</p>
                <p class="text-sm text-slate-200 bg-slate-950/30 px-3 py-2 rounded-lg">{{ $mapel->jenis?->nama ?? 'Tidak Ditentukan' }}</p>
            </div>
        </div>

        <!-- Nama Lengkap -->
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nama Mata Pelajaran</p>
            <p class="text-sm text-slate-200 bg-slate-950/30 px-3 py-2 rounded-lg">{{ $mapel->nama }}</p>
        </div>

        <!-- Jenjang & KKM -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Jenjang</p>
                <p class="text-sm text-slate-200 bg-slate-950/30 px-3 py-2 rounded-lg">{{ $mapel->jenjang ?? 'Semua Jenjang' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">KKM (Kriteria Ketuntasan Minimal)</p>
                <p class="text-sm text-indigo-400 bg-indigo-950/30 px-3 py-2 rounded-lg font-semibold">{{ $mapel->kkm ?? '70' }}</p>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-800">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Dibuat Pada</p>
                <p class="text-xs text-slate-500">{{ $mapel->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Diperbarui Pada</p>
                <p class="text-xs text-slate-500">{{ $mapel->updated_at->format('d M Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end gap-3">
        @can('update', $mapel)
            <a href="{{ route('academic.mapel.edit', $mapel) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-md shadow-indigo-600/20 transition">
                <i class="fas fa-edit"></i> Edit
            </a>
        @endcan
        @can('delete', $mapel)
            <form method="POST" action="{{ route('academic.mapel.destroy', $mapel) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-medium text-sm shadow-md shadow-rose-600/20 transition">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </form>
        @endcan
    </div>
</div>
@endsection
