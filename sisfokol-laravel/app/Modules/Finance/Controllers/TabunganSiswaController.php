<?php

namespace App\Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Finance\Models\TabunganSiswa;
use App\Modules\Finance\Services\TabunganMutasiService;
use App\Support\Crudlfix\Crudlfix;
use Illuminate\Http\Request;

class TabunganSiswaController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => TabunganSiswa::class,
            'view'       => 'finance.tabungan',
            'route'      => 'finance.tabungan',
            'authorize'  => 'tabungan',
            'search'     => ['no_rekening'],
            'with'       => ['siswa'],
            'rules'      => [
                'store' => [
                    'siswa_id' => 'required|integer|exists:siswa,id',
                ],
            ],
            'viewData' => [
                'siswaWithoutTabungan' => Siswa::whereDoesntHave('tabunganSiswa')->get(),
            ],
        ];
    }

    /**
     * Override store to use TabunganMutasiService.
     */
    public function store(Request $request)
    {
        $cfg = $this->config();

        if ($cfg->authorize) {
            \Illuminate\Support\Facades\Gate::authorize("{$cfg->authorize}.create");
        }

        $validated = $this->validateCrudlfix($request, 'store');
        $siswa = Siswa::findOrFail($validated['siswa_id']);
        $service = app(TabunganMutasiService::class);
        $tabungan = $service->getOrCreateAccount($siswa);

        return redirect()
            ->route("{$cfg->route}.index")
            ->with('success', "Rekening tabungan untuk {$siswa->nama} berhasil dibuat dengan nomor: {$tabungan->no_rekening}.");
    }

    /**
     * Custom method: Setor tabungan.
     */
    public function setor(Request $request, TabunganSiswa $tabungan, TabunganMutasiService $service)
    {
        $cfg = $this->config();
        if ($cfg->authorize) {
            \Illuminate\Support\Facades\Gate::authorize("{$cfg->authorize}.update", $tabungan);
        }

        $request->validate(['nominal' => 'required|numeric|gt:0']);

        try {
            $service->setor($tabungan, (float) $request->input('nominal'));
            return redirect()
                ->route("{$cfg->route}.show", $tabungan->id)
                ->with('success', "Setoran tabungan senilai Rp " . number_format($request->input('nominal'), 0, ',', '.') . " berhasil diproses.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Custom method: Tarik tabungan.
     */
    public function tarik(Request $request, TabunganSiswa $tabungan, TabunganMutasiService $service)
    {
        $cfg = $this->config();
        if ($cfg->authorize) {
            \Illuminate\Support\Facades\Gate::authorize("{$cfg->authorize}.update", $tabungan);
        }

        $request->validate(['nominal' => 'required|numeric|gt:0']);

        try {
            $service->tarik($tabungan, (float) $request->input('nominal'));
            return redirect()
                ->route("{$cfg->route}.show", $tabungan->id)
                ->with('success', "Penarikan tabungan senilai Rp " . number_format($request->input('nominal'), 0, ',', '.') . " berhasil diproses.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
