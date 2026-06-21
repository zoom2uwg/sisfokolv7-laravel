@extends('layouts.app')

@section('title', 'Ajukan Izin — SISFOKOL')
@section('page-title', '📝 Form Pengajuan Izin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="rounded-3xl bg-slate-900/80 border border-slate-800 p-8 backdrop-blur-sm shadow-2xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                <i class="fas fa-file-medical text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-100">Ajukan Izin Baru</h1>
                <p class="text-sm text-slate-500">Isi form dengan data yang lengkap dan benar</p>
            </div>
        </div>

        @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-950/40 border border-rose-800/60 text-rose-300">
            <div class="flex items-center gap-2 font-semibold mb-2"><i class="fas fa-exclamation-triangle"></i> Ada kesalahan input:</div>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('presence.izin.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Siswa --}}
            <div>
                <label for="siswa_id" class="block text-sm font-semibold text-slate-300 mb-2">Siswa <span class="text-rose-400">*</span></label>
                <select id="siswa_id" name="siswa_id"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition @error('siswa_id') border-rose-500 @enderror">
                    <option value="">— Pilih Siswa —</option>
                    @foreach($siswaList as $siswa)
                    <option value="{{ $siswa->id }}" @selected(old('siswa_id') == $siswa->id)>
                        {{ $siswa->nama }} ({{ $siswa->nis }})
                    </option>
                    @endforeach
                </select>
                @error('siswa_id')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Tanggal --}}
                <div>
                    <label for="date" class="block text-sm font-semibold text-slate-300 mb-2">Tanggal Izin <span class="text-rose-400">*</span></label>
                    <input type="date" id="date" name="date" value="{{ old('date', today()->toDateString()) }}"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition @error('date') border-rose-500 @enderror">
                    @error('date')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                {{-- Jenis --}}
                <div>
                    <label for="type" class="block text-sm font-semibold text-slate-300 mb-2">Jenis Izin <span class="text-rose-400">*</span></label>
                    <select id="type" name="type"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition @error('type') border-rose-500 @enderror">
                        <option value="">— Pilih Jenis —</option>
                        <option value="sick"       @selected(old('type') === 'sick')>🤒 Sakit</option>
                        <option value="permission" @selected(old('type') === 'permission')>💼 Keperluan</option>
                        <option value="other"      @selected(old('type') === 'other')>📎 Lainnya</option>
                    </select>
                    @error('type')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Alasan --}}
            <div>
                <label for="reason" class="block text-sm font-semibold text-slate-300 mb-2">Alasan / Keterangan <span class="text-rose-400">*</span></label>
                <textarea id="reason" name="reason" rows="4" placeholder="Tuliskan keterangan izin dengan lengkap..."
                    class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-slate-100 text-sm placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition resize-none @error('reason') border-rose-500 @enderror">{{ old('reason') }}</textarea>
                @error('reason')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            {{-- Lampiran --}}
            <div>
                <label for="attachment" class="block text-sm font-semibold text-slate-300 mb-2">
                    Lampiran <span class="text-slate-500 font-normal">(Surat/Foto, opsional)</span>
                </label>
                <div class="relative">
                    <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-slate-400 text-sm
                               file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0
                               file:bg-indigo-600 file:text-white file:text-sm file:font-semibold
                               hover:file:bg-indigo-500 transition @error('attachment') border-rose-500 @enderror">
                </div>
                <p class="mt-1 text-xs text-slate-500">Format: JPG, PNG, atau PDF. Maks. 2 MB.</p>
                @error('attachment')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('presence.izin.index') }}"
                    class="px-6 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-sm font-semibold shadow-lg shadow-indigo-500/20 transition flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
