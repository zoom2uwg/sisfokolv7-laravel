@extends('layouts.app')

@section('title', 'Daftar Absensi — SISFOKOL')
@section('page-title', '🗒️ Daftar Absensi Siswa')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- ─── Header ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">Absensi Siswa</h1>
            <p class="text-sm text-slate-500 mt-0.5">Catatan ketidakhadiran siswa tanpa keterangan</p>
        </div>
        @can('create', \App\Models\Absence::class)
        <a href="{{ route('presence.absensi.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-500 hover:to-pink-500 text-white text-sm font-semibold shadow-lg shadow-rose-500/20 transition">
            <i class="fas fa-user-times"></i> Catat Absensi
        </a>
        @endcan
    </div>

    {{-- ─── Alerts ─── --}}
    @if(session('success'))
    <div class="rounded-2xl border border-emerald-800/60 bg-emerald-950/40 px-5 py-3.5 text-emerald-400 text-sm flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-500"></i>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-2xl border border-rose-800/60 bg-rose-950/40 px-5 py-3.5 text-rose-400 text-sm flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-rose-500"></i>
        {{ session('error') }}
    </div>
    @endif

    {{-- ─── Filter ─── --}}
    <form method="GET" action="{{ route('presence.absensi.index') }}"
        class="rounded-2xl bg-slate-900/80 border border-slate-800 p-4 backdrop-blur-sm flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Cari Nama Siswa</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama siswa…"
                class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition">
        </div>
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Tanggal</label>
            <input type="date" name="date" value="{{ request('date') }}"
                class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-100 text-sm focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition">
        </div>
        <div class="flex gap-2">
            <button type="submit"
                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition flex items-center gap-2">
                <i class="fas fa-search"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'date']))
            <a href="{{ route('presence.absensi.index') }}"
                class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm transition flex items-center gap-2">
                <i class="fas fa-times"></i> Reset
            </a>
            @endif
        </div>
    </form>

    {{-- ─── Table ─── --}}
    <div class="rounded-3xl bg-slate-900/80 border border-slate-800 overflow-hidden backdrop-blur-sm shadow-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Siswa</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Alasan</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($absences as $absence)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-rose-600 to-pink-700 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                    {{ substr($absence->attendable?->nama ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-100">{{ $absence->attendable?->nama ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $absence->attendable?->nis ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-slate-300">
                            {{ $absence->date instanceof \Carbon\Carbon ? $absence->date->format('d M Y') : \Carbon\Carbon::parse($absence->date)->format('d M Y') }}
                        </td>
                        <td class="px-5 py-4 text-slate-400 max-w-xs">
                            <span class="truncate block max-w-[240px]">{{ $absence->reason ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $statusMap = [
                                    'absent'   => ['label' => 'Alpa',     'class' => 'bg-rose-950/50 text-rose-400 border-rose-800/60'],
                                    'excused'  => ['label' => 'Diizinkan','class' => 'bg-emerald-950/50 text-emerald-400 border-emerald-800/60'],
                                    'sick'     => ['label' => 'Sakit',    'class' => 'bg-amber-950/50 text-amber-400 border-amber-800/60'],
                                ];
                                $s = $statusMap[$absence->status] ?? ['label' => $absence->status ?? '—', 'class' => 'bg-slate-800 text-slate-400 border-slate-700'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $s['class'] }}">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500 text-xs">
                            {{ $absence->creator?->nama ?? $absence->user?->nama ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-600">
                                <i class="fas fa-user-clock text-4xl"></i>
                                <p class="text-sm">Tidak ada data absensi</p>
                                @can('create', \App\Models\Absence::class)
                                <a href="{{ route('presence.absensi.create') }}"
                                    class="mt-1 text-xs text-rose-400 hover:text-rose-300 underline underline-offset-2 transition">
                                    Catat absensi pertama
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($absences->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $absences->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
