{{-- academic/jadwal/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah Jadwal')
@section('page-title', 'Tambah Jadwal Pelajaran')

@section('content')
<div class="card">
    <form action="{{ route('academic.jadwal.store') }}" method="POST">
        @csrf
        <div class="card-body space-y-4">
            {{-- Cascading: Tahun Ajaran → Semester --}}
            <x-crudlfix.select
                name="tahun_ajaran_id"
                :options="$tahunAjarans"
                label="Tahun Ajaran"
                :required="true"
                x-on:change="$dispatch('cascade-tahun_ajaran_id', $event.target.value)" />

            <x-crudlfix.cascade-select
                name="semester_id"
                dependsOn="tahun_ajaran_id"
                url="{{ route('academic.jadwal.api') }}"
                field="tahun_ajaran_id"
                label="Semester"
                :required="true" />

            {{-- Standard selects --}}
            <x-crudlfix.select name="kelas_id" :options="$kelasList" label="Kelas" :required="true" />
            <x-crudlfix.select name="mapel_id" :options="$mapels" label="Mapel" :required="true" />

            {{-- Search select for guru --}}
            <x-crudlfix.search-select
                name="guru_id"
                url="{{ route('academic.jadwal.api') }}"
                field="guru_id"
                label="Cari guru..."
                :required="true" />

            {{-- Other fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Hari <span class="text-rose-500">*</span>
                    </label>
                    <select name="hari" required
                        class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">-- Pilih --</option>
                        @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'] as $k => $v)
                        <option value="{{ $k }}" {{ old('hari') == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('hari')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Jam Ke <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="jam_ke" min="1" max="10" value="{{ old('jam_ke') }}" required
                        class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    @error('jam_ke')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Jam Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" required
                        class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    @error('jam_mulai')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Jam Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}" required
                        class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    @error('jam_selesai')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ruang</label>
                <input type="text" name="ruang" maxlength="30" value="{{ old('ruang') }}"
                    class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                @error('ruang')
                    <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>
        </div>
    </form>
</div>
@endsection
