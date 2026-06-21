<?php

namespace App\Plugins\Kurikulum\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Kurikulum\Models\{Kurikulum, StrukturKurikulum, KomponenKompetensi};
use App\Support\TenantContext;
use Illuminate\Http\Request;

class KomponenKompetensiController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Kurikulum::class);

        $komponenList = KomponenKompetensi::with('struktur.kurikulum')
            ->orderBy('kode_kompetensi')
            ->paginate(20);

        return view('kurikulum::komponen.index', compact('komponenList'));
    }

    public function create()
    {
        $this->authorize('create', Kurikulum::class);

        $strukturOptions = StrukturKurikulum::with('kurikulum')
            ->get()
            ->mapWithKeys(fn($s) => [
                $s->id => "{$s->kurikulum->nama_kurikulum} — {$s->jenjang} Kelas {$s->kelas}" . ($s->fase ? " (Fase {$s->fase})" : ''),
            ]);

        return view('kurikulum::komponen.create', compact('strukturOptions'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Kurikulum::class);

        $validated = $request->validate([
            'struktur_id'           => 'required|exists:struktur_kurikulum,id',
            'kode_kompetensi'       => 'required|string|max:30',
            'teks_kompetensi'       => 'required|string|max:1000',
            'pendekatan_pedagogis'  => 'nullable|string|max:50',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id;

        KomponenKompetensi::create($validated);

        return redirect()
            ->route('kurikulum.komponen.index')
            ->with('success', 'Komponen kompetensi berhasil ditambahkan.');
    }

    public function edit(KomponenKompetensi $komponen)
    {
        $this->authorize('update', $komponen->struktur->kurikulum);

        $strukturOptions = StrukturKurikulum::with('kurikulum')
            ->get()
            ->mapWithKeys(fn($s) => [
                $s->id => "{$s->kurikulum->nama_kurikulum} — {$s->jenjang} Kelas {$s->kelas}" . ($s->fase ? " (Fase {$s->fase})" : ''),
            ]);

        return view('kurikulum::komponen.edit', compact('komponen', 'strukturOptions'));
    }

    public function update(Request $request, KomponenKompetensi $komponen)
    {
        $this->authorize('update', $komponen->struktur->kurikulum);

        $validated = $request->validate([
            'struktur_id'           => 'required|exists:struktur_kurikulum,id',
            'kode_kompetensi'       => 'required|string|max:30',
            'teks_kompetensi'       => 'required|string|max:1000',
            'pendekatan_pedagogis'  => 'nullable|string|max:50',
        ]);

        $komponen->update($validated);

        return redirect()
            ->route('kurikulum.komponen.index')
            ->with('success', 'Komponen kompetensi berhasil diperbarui.');
    }

    public function destroy(KomponenKompetensi $komponen)
    {
        $this->authorize('delete', $komponen->struktur->kurikulum);

        $komponen->delete();

        return redirect()
            ->route('kurikulum.komponen.index')
            ->with('success', 'Komponen kompetensi berhasil dihapus.');
    }
}
