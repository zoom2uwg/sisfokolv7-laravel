<?php

namespace App\Plugins\Kurikulum\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Kurikulum\Models\{Kurikulum, StrukturKurikulum};
use App\Support\TenantContext;
use Illuminate\Http\Request;

class StrukturKurikulumController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Kurikulum::class);

        $strukturList = StrukturKurikulum::with('kurikulum')
            ->orderBy('jenjang')
            ->orderBy('kelas')
            ->paginate(15);

        return view('kurikulum::struktur.index', compact('strukturList'));
    }

    public function create()
    {
        $this->authorize('create', Kurikulum::class);

        $kurikulumOptions = Kurikulum::where('status_aktif', true)
            ->orderBy('nama_kurikulum')
            ->pluck('nama_kurikulum', 'id');

        return view('kurikulum::struktur.create', compact('kurikulumOptions'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Kurikulum::class);

        $validated = $request->validate([
            'kurikulum_id'    => 'required|exists:kurikulum,id',
            'jenjang'         => 'required|in:SD,SMP,SMA,SMK',
            'kelas'           => 'required|string|max:5',
            'fase'            => 'nullable|in:A,B,C,D,E,F',
            'jenis_kegiatan'  => 'required|in:intrakurikuler,kokurikuler,ekstrakurikuler',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id;

        StrukturKurikulum::create($validated);

        return redirect()
            ->route('kurikulum.struktur.index')
            ->with('success', 'Struktur kurikulum berhasil ditambahkan.');
    }

    public function edit(StrukturKurikulum $struktur)
    {
        $this->authorize('update', $struktur->kurikulum);

        $kurikulumOptions = Kurikulum::where('status_aktif', true)
            ->orderBy('nama_kurikulum')
            ->pluck('nama_kurikulum', 'id');

        return view('kurikulum::struktur.edit', compact('struktur', 'kurikulumOptions'));
    }

    public function update(Request $request, StrukturKurikulum $struktur)
    {
        $this->authorize('update', $struktur->kurikulum);

        $validated = $request->validate([
            'kurikulum_id'    => 'required|exists:kurikulum,id',
            'jenjang'         => 'required|in:SD,SMP,SMA,SMK',
            'kelas'           => 'required|string|max:5',
            'fase'            => 'nullable|in:A,B,C,D,E,F',
            'jenis_kegiatan'  => 'required|in:intrakurikuler,kokurikuler,ekstrakurikuler',
        ]);

        $struktur->update($validated);

        return redirect()
            ->route('kurikulum.struktur.index')
            ->with('success', 'Struktur kurikulum berhasil diperbarui.');
    }

    public function destroy(StrukturKurikulum $struktur)
    {
        $this->authorize('delete', $struktur->kurikulum);

        $struktur->delete();

        return redirect()
            ->route('kurikulum.struktur.index')
            ->with('success', 'Struktur kurikulum berhasil dihapus.');
    }
}
