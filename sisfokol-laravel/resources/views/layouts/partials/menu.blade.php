@php
    $menuItems = \App\Support\MenuRenderer::forUser(auth()->user());
    $grouped = $menuItems->groupBy('group');
@endphp

@foreach ($grouped as $groupName => $items)
    @if ($groupName && $groupName !== 'Utama')
        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">{{ $groupName }}</p>
        </div>
    @endif

    @foreach ($items as $item)
        @php
            // Check if route is active
            $isActive = $item->route && (request()->routeIs($item->route) || request()->routeIs(explode('.', $item->route)[0] . '.*'));
        @endphp
        <a href="{{ ($item->route && Route::has($item->route)) ? route($item->route) : '#' }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition {{ $isActive ? 'bg-indigo-950 text-indigo-400 border border-indigo-900/50' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
            <i class="{{ $item->icon ?? 'fas fa-link' }} w-5 text-center text-base"></i>
            <span>{{ $item->label }}</span>
        </a>
    @endforeach
@endforeach
