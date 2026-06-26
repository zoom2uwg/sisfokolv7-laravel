@extends('layouts.app')

@section('title', 'Struktur Kurikulum')
@section('page-title', 'Struktur Kurikulum')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-teal-600 shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                    </svg>
                </span>
                Struktur Kurikulum
            </h2>
            <p class="text-slate-400 mt-1 text-sm">Pemetaan jenjang, kelas, fase, dan jenis kegiatan per kurikulum</p>
        </div>
        @can('create', \App\Plugins\Kurikulum\Models\Kurikulum::class)
        <a href="{{ route('kurikulum.struktur.create') }}"
           id="btn-tambah-struktur"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-teal-600 hover:from-cyan-500 hover:to-teal-500 text-white text-sm font-semibold shadow-lg shadow-cyan-900/40 transition-all duration-200 hover:scale-[1.02] active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Struktur
        </a>
        @endcan
    </div>

    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3500)"
         class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-slate-900/60 backdrop-blur-sm border border-slate-700/50 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/50 bg-slate-800/50">
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-10">#</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Kurikulum</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Jenjang</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Kelas</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Fase</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Jenis Kegiatan</th>
                        <th class="px-5 py-4 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    @forelse ($strukturList as $idx => $str)
                    <tr class="hover:bg-slate-800/40 transition-colors duration-150 group">
                        <td class="px-5 py-4 text-slate-500 font-mono text-xs">{{ $strukturList->firstItem() + $idx }}</td>
                        <td class="px-5 py-4">
                            <span class="text-slate-200 font-medium">{{ $str->kurikulum->nama_kurikulum ?? '—' }}</span>
                            <span class="ml-2 text-xs text-slate-500 font-mono">({{ $str->kurikulum->kurikulum_id ?? '' }})</span>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $jenjangColor = match($str->jenjang) {
                                    'SD' => 'bg-green-500/10 border-green-500/25 text-green-300',
                                    'SMP' => 'bg-blue-500/10 border-blue-500/25 text-blue-300',
                                    'SMA' => 'bg-purple-500/10 border-purple-500/25 text-purple-300',
                                    'SMK' => 'bg-orange-500/10 border-orange-500/25 text-orange-300',
                                    default => 'bg-slate-700/40 border-slate-600/40 text-slate-300',
                                };
                            @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-lg border {{ $jenjangColor }} text-xs font-semibold">
                                {{ $str->jenjang }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-300 font-mono text-sm">{{ $str->kelas }}</td>
                        <td class="px-5 py-4">
                            @if($str->fase)
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-cyan-500/10 border border-cyan-500/25 text-cyan-300 text-xs font-bold">
                                {{ $str->fase }}
                            </span>
                            @else
                            <span class="text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $kegColor = match($str->jenis_kegiatan) {
                                    'intrakurikuler' => 'bg-emerald-500/10 border-emerald-500/25 text-emerald-300',
                                    'kokurikuler_p5' => 'bg-sky-500/10 border-sky-500/25 text-sky-300',
                                    'ekstrakurikuler' => 'bg-pink-500/10 border-pink-500/25 text-pink-300',
                                    default => 'bg-slate-700/40 border-slate-600/40 text-slate-300',
                                };
                            @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-lg border {{ $kegColor }} text-xs font-medium capitalize">
                                {{ $str->jenis_kegiatan }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                @can('update', $str->kurikulum)
                                <a href="{{ route('kurikulum.struktur.edit', $str) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-medium hover:bg-amber-500/20 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    Edit
                                </a>
                                @endcan
                                @can('delete', $str->kurikulum)
                                <form action="{{ route('kurikulum.struktur.destroy', $str) }}" method="POST"
                                      onsubmit="return confirm('Hapus struktur ini? Komponen kompetensi terkait juga akan terhapus.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-medium hover:bg-red-500/20 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        Hapus
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-500">
                                <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25z"/></svg>
                                <p class="text-sm">Belum ada struktur kurikulum</p>
                                @can('create', \App\Plugins\Kurikulum\Models\Kurikulum::class)
                                <a href="{{ route('kurikulum.struktur.create') }}" class="text-cyan-400 hover:text-cyan-300 text-sm font-medium">+ Tambah struktur pertama</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($strukturList->hasPages())
        <div class="px-6 py-4 border-t border-slate-700/50">{{ $strukturList->links() }}</div>
        @endif
    </div>

</div>
@endsection
