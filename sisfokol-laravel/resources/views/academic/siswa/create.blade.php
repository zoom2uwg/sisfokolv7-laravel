@extends('layouts.app')

@section('title', 'Akademik — Tambah Siswa')
@section('page-title', 'Tambah Siswa Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumbs / Back button -->
    <div class="flex items-center justify-between pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">Tambah Siswa Baru</h1>
            <p class="text-sm text-slate-400 mt-1">Masukkan informasi profil siswa secara lengkap.</p>
        </div>
        <a href="{{ route('academic.siswa.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-slate-100 rounded-xl text-sm font-medium transition border border-slate-700">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl p-6 shadow-xl">
        <form method="POST" action="{{ route('academic.siswa.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- NIS -->
                <div>
                    <label for="nis" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nomor Induk Siswa (NIS) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nis" id="nis" value="{{ old('nis') }}" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('nis') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                    @error('nis')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NISN -->
                <div>
                    <label for="nisn" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">NISN</label>
                    <input type="text" name="nisn" id="nisn" value="{{ old('nisn') }}" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('nisn') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                    @error('nisn')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Lengkap -->
                <div class="md:col-span-2">
                    <label for="nama" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('nama') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                    @error('nama')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label for="jenis_kelamin" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="jenis_kelamin" id="jenis_kelamin" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('jenis_kelamin') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                    </select>
                    @error('jenis_kelamin')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Agama -->
                <div>
                    <label for="agama" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Agama</label>
                    <select name="agama" id="agama" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('agama') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                        <option value="">-- Pilih Agama --</option>
                        @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $a)
                            <option value="{{ $a }}" {{ old('agama') === $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                    @error('agama')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label for="tempat_lahir" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" value="{{ old('tempat_lahir') }}" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('tempat_lahir') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                    @error('tempat_lahir')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Lahir (Field ACL) -->
                @field('siswa.tanggal_lahir')
                <div>
                    <label for="tanggal_lahir" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="{{ old('tanggal_lahir') }}" @fieldAttr('siswa.tanggal_lahir') class="w-full px-4 py-2.5 bg-slate-950/50 border @error('tanggal_lahir') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    @error('tanggal_lahir')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
                @endfield

                <!-- Telepon (Field ACL) -->
                @field('siswa.telepon')
                    <div>
                        <label for="telepon" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nomor Telepon</label>
                        <input type="text" name="telepon" id="telepon" value="{{ old('telepon') }}" @fieldAttr('siswa.telepon') class="w-full px-4 py-2.5 bg-slate-950/50 border @error('telepon') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        @error('telepon')
                            <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                @endfield

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Status Siswa <span class="text-rose-500">*</span></label>
                    <select name="status" id="status" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('status') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                        @foreach(['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah', 'keluar' => 'Keluar'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('status', 'aktif') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat (Field ACL) -->
                @field('siswa.alamat')
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Alamat Lengkap</label>
                    <textarea name="alamat" id="alamat" rows="3" @fieldAttr('siswa.alamat') class="w-full px-4 py-2.5 bg-slate-950/50 border @error('alamat') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm disabled:opacity-50 disabled:cursor-not-allowed">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Action buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('academic.siswa.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-800 text-slate-450 hover:text-slate-200 hover:bg-slate-800/55 transition text-sm font-medium">
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
