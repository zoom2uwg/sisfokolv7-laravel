@extends('layouts.app')

@section('title', 'Edit Struktur Kurikulum')
@section('page-title', 'Edit Struktur Kurikulum')

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
        <div class="px-6 py-5 border-b border-slate-700/50 bg-gradient-to-r from-amber-900/20 to-orange-900/10">
            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                </svg>
                Edit Struktur: <span class="text-amber-300">{{ $struktur->jenjang }} Kelas {{ $struktur->kelas }}</span>
            </h3>
        </div>

        <form action="{{ route('kurikulum.struktur.update', $struktur) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="kurikulum_id" class="block text-sm font-medium text-slate-300">Kurikulum <span class="text-red-400">*</span></label>
                <select id="kurikulum_id" name="kurikulum_id"
                        class="w-full px-4 py-2.5 bg-slate-800/70 border border-slate-600/50 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all text-sm">
                    @foreach ($kurikulumOptions as $id => $nama)
                    <option value="{{ $id }}" {{ old('kurikulum_id', $struktur->kurikulum_id) == $id ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
                @error('kurikulum_id') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="jenjang" class="block text-sm font-medium text-slate-300">Jenjang <span class="text-red-400">*</span></label>
                    <select id="jenjang" name="jenjang"
                            class="w-full px-4 py-2.5 bg-slate-800/70 border border-slate-600/50 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all text-sm">
                        @foreach(['SD', 'SMP', 'SMA', 'SMK'] as $j)
                        <option value="{{ $j }}" {{ old('jenjang', $struktur->jenjang) == $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label for="kelas" class="block text-sm font-medium text-slate-300">Kelas <span class="text-red-400">*</span></label>
                    <input type="text" id="kelas" name="kelas"
                           value="{{ old('kelas', $struktur->kelas) }}"
                           class="w-full px-4 py-2.5 bg-slate-800/70 border border-slate-600/50 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="fase" class="block text-sm font-medium text-slate-300">Fase</label>
                    <select id="fase" name="fase"
                            class="w-full px-4 py-2.5 bg-slate-800/70 border border-slate-600/50 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all text-sm">
                        <option value="">— Tidak Ada —</option>
                        @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $f)
                        <option value="{{ $f }}" {{ old('fase', $struktur->fase) == $f ? 'selected' : '' }}>Fase {{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label for="jenis_kegiatan" class="block text-sm font-medium text-slate-300">Jenis Kegiatan <span class="text-red-400">*</span></label>
                    <select id="jenis_kegiatan" name="jenis_kegiatan"
                            class="w-full px-4 py-2.5 bg-slate-800/70 border border-slate-600/50 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all text-sm">
                        <option value="intrakurikuler" {{ old('jenis_kegiatan', $struktur->jenis_kegiatan) == 'intrakurikuler' ? 'selected' : '' }}>Intrakurikuler</option>
                        <option value="kokurikuler_p5" {{ old('jenis_kegiatan', $struktur->jenis_kegiatan) == 'kokurikuler_p5' ? 'selected' : '' }}>Kokurikuler (P5)</option>
                        <option value="ekstrakurikuler" {{ old('jenis_kegiatan', $struktur->jenis_kegiatan) == 'ekstrakurikuler' ? 'selected' : '' }}>Ekstrakurikuler</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('kurikulum.struktur.index') }}"
                   class="px-5 py-2.5 rounded-xl border border-slate-600/60 text-slate-300 text-sm font-medium hover:bg-slate-800/60 transition-colors">
                    Batal
                </a>
                <button type="submit" id="btn-update-struktur"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-white text-sm font-semibold shadow-lg shadow-amber-900/40 transition-all duration-200 hover:scale-[1.02] active:scale-95">
                    Perbarui Struktur
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
