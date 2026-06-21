@extends('layouts.app')

@section('title', 'Penilaian Akademik — SISFOKOL')
@section('page-title', '📝 Penilaian Akademik')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-100">Lembar Penilaian</h1>
        <p class="text-sm text-slate-500 mt-0.5">Pilih kelas dan mata pelajaran untuk melakukan entri nilai siswa.</p>
    </div>

    {{-- Form Card --}}
    <div class="rounded-3xl bg-slate-900/80 border border-slate-800 p-8 backdrop-blur-sm shadow-2xl space-y-6">
        <form method="GET" action="{{ route('evaluation.grade-entry.form') }}" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Classroom Selector --}}
                <div class="space-y-2">
                    <label for="classroom_id" class="text-sm font-semibold text-slate-300 flex items-center gap-2">
                        <i class="fas fa-school text-indigo-400"></i> Kelas
                    </label>
                    <select name="classroom_id" id="classroom_id" required
                        class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        <option value="" disabled selected>Pilih Kelas...</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject Selector --}}
                <div class="space-y-2">
                    <label for="subject_id" class="text-sm font-semibold text-slate-300 flex items-center gap-2">
                        <i class="fas fa-book-open text-indigo-400"></i> Mata Pelajaran
                    </label>
                    <select name="subject_id" id="subject_id" required
                        class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        <option value="" disabled selected>Pilih Mata Pelajaran...</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit"
                    class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition flex items-center gap-2 shadow-lg shadow-indigo-600/20">
                    <i class="fas fa-edit"></i> Buka Lembar Nilai
                </button>
            </div>
        </form>
    </div>

    {{-- Info Card --}}
    <div class="rounded-2xl border border-slate-800/80 bg-indigo-950/20 p-4 text-xs text-slate-400 flex gap-3">
        <div class="text-indigo-400 text-sm">
            <i class="fas fa-info-circle"></i>
        </div>
        <div>
            <p class="font-semibold text-slate-300">Informasi Penting</p>
            <p class="mt-0.5">Pastikan Anda memilih kelas dan mata pelajaran yang diampu. Penilaian semester akan dihitung secara otomatis dengan pembobotan 40% rata-rata formatif dan 60% rata-rata sumatif.</p>
        </div>
    </div>
</div>
@endsection
