@extends('layouts.app')

@section('title', 'Akademik — Tambah Mata Pelajaran')
@section('page-title', 'Tambah Mata Pelajaran Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumbs / Back button -->
    <div class="flex items-center justify-between pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">Tambah Mata Pelajaran Baru</h1>
            <p class="text-sm text-slate-400 mt-1">Masukkan informasi mata pelajaran secara lengkap.</p>
        </div>
        <a href="{{ route('academic.mapel.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-slate-100 rounded-xl text-sm font-medium transition border border-slate-700">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl p-6 shadow-xl">
        <form method="POST" action="{{ route('academic.mapel.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kode Mata Pelajaran -->
                <div>
                    <label for="kode" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kode Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode" id="kode" value="{{ old('kode') }}" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('kode') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                    @error('kode')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Mata Pelajaran -->
                <div>
                    <label for="mapel_jenis_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Jenis Mata Pelajaran</label>
                    <select name="mapel_jenis_id" id="mapel_jenis_id" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('mapel_jenis_id') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                        <option value="">-- Pilih Jenis Mapel --</option>
                        @foreach($jenisList as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('mapel_jenis_id') == $jenis->id ? 'selected' : '' }}>{{ $jenis->nama }}</option>
                        @endforeach
                    </select>
                    @error('mapel_jenis_id')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Mata Pelajaran -->
                <div class="md:col-span-2">
                    <label for="nama" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('nama') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                    @error('nama')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenjang -->
                <div>
                    <label for="jenjang" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Jenjang</label>
                    <select name="jenjang" id="jenjang" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('jenjang') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                        <option value="">-- Pilih Jenjang --</option>
                        <option value="SMP" {{ old('jenjang') === 'SMP' ? 'selected' : '' }}>SMP</option>
                        <option value="SMA" {{ old('jenjang') === 'SMA' ? 'selected' : '' }}>SMA</option>
                        <option value="SMK" {{ old('jenjang') === 'SMK' ? 'selected' : '' }}>SMK</option>
                    </select>
                    @error('jenjang')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- KKM (Kriteria Ketuntasan Minimal) -->
                <div>
                    <label for="kkm" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">KKM (Kriteria Ketuntasan Minimal)</label>
                    <input type="number" name="kkm" id="kkm" value="{{ old('kkm', 70) }}" min="0" max="100" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('kkm') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                    @error('kkm')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Action buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('academic.mapel.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-800 text-slate-450 hover:text-slate-200 hover:bg-slate-800/55 transition text-sm font-medium">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
