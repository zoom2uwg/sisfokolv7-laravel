@extends('layouts.app')

@section('title', 'Tambah Struktur Kurikulum')
@section('page-title', 'Tambah Struktur Kurikulum')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <a href="{{ route('kurikulum.struktur.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-slate-200 text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali ke Daftar Struktur
    </a>

    <div class="bg-slate-900/70 backdrop-blur-sm border border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-700/50 bg-gradient-to-r from-cyan-900/20 to-teal-900/10">
            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Tambah Struktur Kurikulum
            </h3>
        </div>

        <form action="{{ route('kurikulum.struktur.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            {{-- Kurikulum --}}
            <div class="space-y-1.5">
                <label for="kurikulum_id" class="block text-sm font-medium text-slate-300">
                    Kurikulum <span class="text-red-400">*</span>
                </label>
                <select id="kurikulum_id" name="kurikulum_id"
                        class="w-full px-4 py-2.5 bg-slate-800/70 border {{ $errors->has('kurikulum_id') ? 'border-red-500/70' : 'border-slate-600/50' }} rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all duration-150 text-sm">
                    <option value="">-- Pilih Kurikulum --</option>
                    @foreach ($kurikulumOptions as $id => $nama)
                    <option value="{{ $id }}" {{ old('kurikulum_id') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
                @error('kurikulum_id') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
            </div>

            {{-- Jenjang & Kelas --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="jenjang" class="block text-sm font-medium text-slate-300">
                        Jenjang <span class="text-red-400">*</span>
                    </label>
                    <select id="jenjang" name="jenjang"
                            class="w-full px-4 py-2.5 bg-slate-800/70 border {{ $errors->has('jenjang') ? 'border-red-500/70' : 'border-slate-600/50' }} rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all duration-150 text-sm">
                        <option value="">-- Pilih --</option>
                        @foreach(['SD', 'SMP', 'SMA', 'SMK'] as $j)
                        <option value="{{ $j }}" {{ old('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                    @error('jenjang') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label for="kelas" class="block text-sm font-medium text-slate-300">
                        Kelas <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="kelas" name="kelas"
                           value="{{ old('kelas') }}"
                           placeholder="Contoh: 7, 10, 1"
                           class="w-full px-4 py-2.5 bg-slate-800/70 border {{ $errors->has('kelas') ? 'border-red-500/70' : 'border-slate-600/50' }} rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all duration-150 text-sm">
                    @error('kelas') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Fase & Jenis Kegiatan --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="fase" class="block text-sm font-medium text-slate-300">
                        Fase
                        <span class="text-xs text-slate-500">(Kurikulum Merdeka)</span>
                    </label>
                    <select id="fase" name="fase"
                            class="w-full px-4 py-2.5 bg-slate-800/70 border border-slate-600/50 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all duration-150 text-sm">
                        <option value="">— Tidak Ada —</option>
                        @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $f)
                        <option value="{{ $f }}" {{ old('fase') == $f ? 'selected' : '' }}>Fase {{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label for="jenis_kegiatan" class="block text-sm font-medium text-slate-300">
                        Jenis Kegiatan <span class="text-red-400">*</span>
                    </label>
                    <select id="jenis_kegiatan" name="jenis_kegiatan"
                            class="w-full px-4 py-2.5 bg-slate-800/70 border {{ $errors->has('jenis_kegiatan') ? 'border-red-500/70' : 'border-slate-600/50' }} rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all duration-150 text-sm">
                        <option value="">-- Pilih --</option>
                        <option value="intrakurikuler" {{ old('jenis_kegiatan') == 'intrakurikuler' ? 'selected' : '' }}>Intrakurikuler</option>
                        <option value="kokurikuler_p5" {{ old('jenis_kegiatan') == 'kokurikuler_p5' ? 'selected' : '' }}>Kokurikuler (P5)</option>
                        <option value="ekstrakurikuler" {{ old('jenis_kegiatan') == 'ekstrakurikuler' ? 'selected' : '' }}>Ekstrakurikuler</option>
                    </select>
                    @error('jenis_kegiatan') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('kurikulum.struktur.index') }}"
                   class="px-5 py-2.5 rounded-xl border border-slate-600/60 text-slate-300 text-sm font-medium hover:bg-slate-800/60 transition-colors">
                    Batal
                </a>
                <button type="submit" id="btn-simpan-struktur"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-teal-600 hover:from-cyan-500 hover:to-teal-500 text-white text-sm font-semibold shadow-lg shadow-cyan-900/40 transition-all duration-200 hover:scale-[1.02] active:scale-95">
                    Simpan Struktur
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
