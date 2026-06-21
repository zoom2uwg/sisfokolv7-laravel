@extends('layouts.app')

@section('title', 'Detail Izin — SISFOKOL')
@section('page-title', '🔎 Detail Pengajuan Izin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Back --}}
    <div>
        <a href="{{ route('presence.izin.index') }}"
            class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-300 transition">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Izin
        </a>
    </div>

    {{-- Main Detail Card --}}
    <div class="rounded-3xl bg-slate-900/80 border border-slate-800 overflow-hidden backdrop-blur-sm shadow-2xl">
        {{-- Header gradient --}}
        @php
            $headerColors = [
                'pending'  => 'from-amber-900/60 to-amber-800/30',
                'approved' => 'from-emerald-900/60 to-emerald-800/30',
                'rejected' => 'from-rose-900/60 to-rose-800/30',
            ];
            $headerColor = $headerColors[$permit->status] ?? 'from-slate-800/60 to-slate-700/30';

            $statusLabels = [
                'pending'  => ['text' => 'Menunggu Persetujuan', 'icon' => 'clock', 'badge' => 'bg-amber-950/60 text-amber-300 border-amber-700/60'],
                'approved' => ['text' => 'Disetujui', 'icon' => 'check-circle', 'badge' => 'bg-emerald-950/60 text-emerald-300 border-emerald-700/60'],
                'rejected' => ['text' => 'Ditolak', 'icon' => 'times-circle', 'badge' => 'bg-rose-950/60 text-rose-300 border-rose-700/60'],
            ];
            $statusInfo = $statusLabels[$permit->status] ?? ['text' => $permit->status, 'icon' => 'question', 'badge' => 'bg-slate-800 text-slate-400 border-slate-700'];

            $typeLabels = ['sick' => 'Sakit', 'permission' => 'Keperluan', 'other' => 'Lainnya'];
        @endphp

        <div class="bg-gradient-to-r {{ $headerColor }} px-8 py-6 border-b border-slate-800">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-slate-900/60 border border-slate-700/50 flex items-center justify-center text-2xl font-bold text-slate-100">
                        {{ substr($permit->permitable?->nama ?? '?', 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-100">{{ $permit->permitable?->nama ?? '—' }}</h2>
                        <p class="text-sm text-slate-400">NIS: {{ $permit->permitable?->nis ?? '—' }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border text-sm font-semibold {{ $statusInfo['badge'] }}">
                    <i class="fas fa-{{ $statusInfo['icon'] }}"></i> {{ $statusInfo['text'] }}
                </span>
            </div>
        </div>

        {{-- Details --}}
        <div class="p-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Tanggal Izin</p>
                <p class="text-slate-200 font-medium">{{ $permit->date?->format('l, d F Y') }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Jenis Izin</p>
                <p class="text-slate-200 font-medium">{{ $typeLabels[$permit->type] ?? $permit->type }}</p>
            </div>
            <div class="sm:col-span-2 space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Alasan / Keterangan</p>
                <p class="text-slate-200 leading-relaxed">{{ $permit->reason }}</p>
            </div>

            @if($permit->attachment_path)
            <div class="sm:col-span-2 space-y-2">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Lampiran</p>
                <a href="{{ Storage::url($permit->attachment_path) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-sm font-medium transition">
                    <i class="fas fa-paperclip text-indigo-400"></i> Lihat Lampiran
                </a>
            </div>
            @endif

            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Diajukan Oleh</p>
                <p class="text-slate-200 font-medium">{{ $permit->user?->nama ?? '—' }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Waktu Pengajuan</p>
                <p class="text-slate-200 font-medium">{{ $permit->created_at?->format('d M Y, H:i') }}</p>
            </div>

            @if($permit->status !== 'pending')
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Diproses Oleh</p>
                <p class="text-slate-200 font-medium">{{ $permit->approver?->nama ?? '—' }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Waktu Proses</p>
                <p class="text-slate-200 font-medium">{{ $permit->approved_at?->format('d M Y, H:i') }}</p>
            </div>
            @endif

            @if($permit->status === 'rejected' && $permit->note)
            <div class="sm:col-span-2 p-4 rounded-2xl bg-rose-950/30 border border-rose-800/50">
                <p class="text-xs font-semibold text-rose-400 uppercase tracking-wide mb-1">Alasan Penolakan</p>
                <p class="text-rose-200 text-sm">{{ $permit->note }}</p>
            </div>
            @endif
        </div>

        {{-- Approval Actions --}}
        @if($permit->status === 'pending' && auth()->user()->can('approve', $permit))
        <div class="px-8 pb-8 flex flex-col sm:flex-row gap-3" x-data="{ showRejectForm: false }">
            {{-- Approve button --}}
            <form action="{{ route('presence.izin.approve', $permit) }}" method="POST" class="flex-1">
                @csrf
                <button type="submit"
                    onclick="return confirm('Setujui pengajuan izin ini?')"
                    class="w-full py-3 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-sm font-semibold shadow-lg shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Setujui Izin
                </button>
            </form>

            {{-- Reject toggle --}}
            <button type="button" @click="showRejectForm = !showRejectForm"
                class="flex-1 py-3 px-6 rounded-2xl bg-rose-950/50 hover:bg-rose-900/60 border border-rose-800/60 text-rose-400 hover:text-rose-300 text-sm font-semibold transition flex items-center justify-center gap-2">
                <i class="fas fa-times-circle"></i> Tolak Izin
            </button>
        </div>

        {{-- Rejection reason form --}}
        <div x-show="showRejectForm" x-cloak class="px-8 pb-8">
            <form action="{{ route('presence.izin.reject', $permit) }}" method="POST" class="p-4 rounded-2xl bg-slate-800/60 border border-rose-800/40">
                @csrf
                <label for="rejection_reason" class="block text-sm font-semibold text-slate-300 mb-2">
                    <i class="fas fa-comment-alt text-rose-400 mr-1"></i> Alasan Penolakan
                </label>
                <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                    placeholder="Tuliskan alasan penolakan..."
                    class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 text-sm placeholder-slate-600 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition resize-none"></textarea>
                <div class="mt-3 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Kirim Penolakan
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
