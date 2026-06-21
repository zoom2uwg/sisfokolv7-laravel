@extends('layouts.app')

@section('title', 'RBAC Builder — Roles')
@section('page-title', 'RBAC Builder')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Breadcrumbs / Navigation Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">Role & Permission Matrix</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola perizinan akses secara real-time untuk masing-masing role.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-800">
        <nav class="flex space-x-8" aria-label="Tabs">
            <a href="{{ route('rbac.index') }}" class="border-indigo-500 text-indigo-400 border-b-2 py-4 px-1 text-sm font-medium">Roles & Permissions</a>
            <a href="{{ route('rbac.menus') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">Menu Visibility</a>
            <a href="{{ route('rbac.fields') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">Field Visibility</a>
            <a href="{{ route('rbac.users') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">User Roles</a>
        </nav>
    </div>

    <!-- Info Box -->
    <div class="p-4 rounded-2xl bg-indigo-950/20 border border-indigo-800/40 text-indigo-300 text-sm flex items-start gap-3">
        <i class="fas fa-info-circle text-lg mt-0.5"></i>
        <div>
            <span class="font-semibold">Info Penggunaan:</span>
            <p class="mt-0.5 text-indigo-400/90">Klik pada checkbox matriks untuk mengaktifkan atau menonaktifkan izin. Perubahan akan disimpan secara otomatis menggunakan AJAX dengan indikator visual.</p>
        </div>
    </div>

    <!-- Matrix Card -->
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950/50 border-b border-slate-800/60">
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider min-w-[200px]">Role \ Permission</th>
                        @foreach($permissions as $p)
                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center select-none" title="{{ $p->name }}">
                                <div class="transform -rotate-12 origin-bottom-left py-1 text-xs truncate max-w-[120px]">{{ explode('.', $p->name)[1] ?? $p->name }}</div>
                                <div class="text-[9px] text-slate-600 lowercase mt-1">{{ explode('.', $p->name)[0] }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @foreach($roles as $role)
                        <tr class="hover:bg-slate-800/20 transition">
                            <td class="p-4">
                                <span class="text-sm font-semibold text-slate-200 block">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
                                <span class="text-[10px] text-slate-500 uppercase tracking-wider">{{ $role->guard_name }}</span>
                            </td>
                            @foreach($permissions as $p)
                                @php $has = $role->permissions->contains($p->id); @endphp
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <input type="checkbox" 
                                            class="rbac-cell h-4 w-4 rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900 focus:ring-offset-2 transition cursor-pointer"
                                            data-role="{{ $role->id }}" 
                                            data-perm="{{ $p->id }}"
                                            @if($has) checked @endif>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.rbac-cell').forEach(cb => {
    cb.addEventListener('change', function() {
        const cell = this;
        const roleId = cell.dataset.role;
        const parentRow = cell.closest('tr');
        
        // Add loading indicator class or opacity
        cell.style.opacity = '0.5';
        
        const checked = Array.from(parentRow.querySelectorAll('.rbac-cell:checked')).map(x => parseInt(x.dataset.perm));
        
        fetch(`/admin/rbac/role/${roleId}/permissions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            },
            body: JSON.stringify({permissions: checked})
        })
        .then(r => {
            if (!r.ok) throw new Error('Gagal menyimpan.');
            return r.json();
        })
        .then(d => {
            cell.style.opacity = '1';
            // Subtle green flash on success
            cell.classList.add('ring-2', 'ring-emerald-500');
            setTimeout(() => cell.classList.remove('ring-2', 'ring-emerald-500'), 1000);
        })
        .catch(err => {
            cell.style.opacity = '1';
            cell.checked = !cell.checked; // Revert checkbox state
            cell.classList.add('ring-2', 'ring-rose-500');
            setTimeout(() => cell.classList.remove('ring-2', 'ring-rose-500'), 1500);
            alert('Kesalahan: Perubahan RBAC tidak diizinkan atau diblokir.');
        });
    });
});
</script>
@endpush
@endsection
