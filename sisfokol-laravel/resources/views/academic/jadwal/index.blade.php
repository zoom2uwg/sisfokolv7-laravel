{{-- academic/jadwal/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Jadwal')
@section('page-title', 'Data Jadwal Pelajaran')

@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3 class="card-title">Daftar Jadwal</h3>
        <a href="{{ route('academic.jadwal.create') }}" class="btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Tambah
        </a>
    </div>
    <div class="card-body">
        <x-crudlfix.data-table
            :data="$jadwals->map(fn($j) => [
                'id'             => $j->id,
                'tahunAjaran'    => $j->tahunAjaran?->nama ?? '-',
                'semester'       => $j->semester?->nama ?? '-',
                'kelas'          => $j->kelas?->nama ?? '-',
                'mapel'          => $j->mapel?->nama ?? '-',
                'guru'           => $j->guru?->nama ?? '-',
                'hari'           => ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'][$j->hari - 1] ?? '-',
                'jam'            => $j->jam_mulai . ' - ' . $j->jam_selesai,
            ])"
            :columns="[
                'tahunAjaran' => 'Tahun Ajaran',
                'semester'    => 'Semester',
                'kelas'       => 'Kelas',
                'mapel'       => 'Mapel',
                'guru'        => 'Guru',
                'hari'        => 'Hari',
                'jam'         => 'Jam',
            ]"
            variant="advanced"
            :selectable="true"
            :bulkActions="['delete']"
            :exportable="true"
            :rowActions="['edit', 'delete']">

            <div class="flex gap-2">
                <a :href="'{{ route('academic.jadwal.index') }}/' + row.id + '/edit'"
                    class="text-indigo-400 hover:text-indigo-300 transition">
                    <i class="fas fa-edit"></i>
                </a>
                <form :action="'{{ route('academic.jadwal.index') }}/' + row.id" method="POST" class="inline"
                    onsubmit="return confirm('Yakin hapus?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-rose-400 hover:text-rose-300 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>

        </x-crudlfix.data-table>
    </div>
</div>
@endsection
