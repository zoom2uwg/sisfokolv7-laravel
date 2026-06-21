<?php

namespace App\Plugins\Kurikulum\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Kurikulum\Models\Kurikulum;
use App\Support\TenantContext;
use Illuminate\Http\Request;

class KurikulumController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Kurikulum::class);

        $kurikulumList = Kurikulum::orderBy('nama_kurikulum')->paginate(15);

        return view('kurikulum::kurikulum.index', compact('kurikulumList'));
    }

    public function create()
    {
        $this->authorize('create', Kurikulum::class);

        return view('kurikulum::kurikulum.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Kurikulum::class);

        $validated = $request->validate([
            'kurikulum_id'  => 'required|string|max:20|unique:kurikulum,kurikulum_id',
            'nama_kurikulum'=> 'required|string|max:100',
            'deskripsi'     => 'nullable|string|max:500',
            'status_aktif'  => 'boolean',
        ]);

        $validated['status_aktif'] = $request->boolean('status_aktif');
        $validated['tenant_id']    = app(TenantContext::class)->id;

        Kurikulum::create($validated);

        return redirect()
            ->route('kurikulum.index')
            ->with('success', 'Kurikulum berhasil ditambahkan.');
    }

    public function edit(Kurikulum $kurikulum)
    {
        $this->authorize('update', $kurikulum);

        return view('kurikulum::kurikulum.edit', compact('kurikulum'));
    }

    public function update(Request $request, Kurikulum $kurikulum)
    {
        $this->authorize('update', $kurikulum);

        $validated = $request->validate([
            'kurikulum_id'  => 'required|string|max:20|unique:kurikulum,kurikulum_id,' . $kurikulum->id,
            'nama_kurikulum'=> 'required|string|max:100',
            'deskripsi'     => 'nullable|string|max:500',
            'status_aktif'  => 'boolean',
        ]);

        $validated['status_aktif'] = $request->boolean('status_aktif');

        $kurikulum->update($validated);

        return redirect()
            ->route('kurikulum.index')
            ->with('success', 'Kurikulum berhasil diperbarui.');
    }

    public function destroy(Kurikulum $kurikulum)
    {
        $this->authorize('delete', $kurikulum);

        $kurikulum->delete();

        return redirect()
            ->route('kurikulum.index')
            ->with('success', 'Kurikulum berhasil dihapus.');
    }
}
