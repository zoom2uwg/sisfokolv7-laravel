<?php

namespace App\Modules\Presence\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Modules\Academic\Models\Siswa;
use App\Support\Crudlfix\Crudlfix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => Absence::class,
            'view'       => 'presence.absensi',
            'route'      => 'presence.absensi',
            'authorize'  => 'absensi',
            'search'     => [],
            'with'       => ['attendable'],
            'rules'      => [
                'store' => [
                    'permitable_id' => 'required|exists:siswa,id',
                    'date'          => 'required|date',
                    'reason'        => 'required|string|max:500',
                ],
            ],
            'viewData' => [
                'siswaList' => Siswa::where('status', 'aktif')->orderBy('nama')->get(),
            ],
            'perPage' => 25,
        ];
    }

    /**
     * Override store for polymorphic relation.
     */
    public function store(Request $request)
    {
        $cfg = $this->config();
        if ($cfg->authorize) {
            \Illuminate\Support\Facades\Gate::authorize("{$cfg->authorize}.create");
        }

        $validated = $this->validateCrudlfix($request, 'store');
        $siswa = Siswa::findOrFail($validated['permitable_id']);

        Absence::create([
            'user_id'         => Auth::id(),
            'absentable_type' => Siswa::class,
            'absentable_id'   => $siswa->id,
            'date'            => $validated['date'],
            'reason'          => $validated['reason'],
            'status'          => 'absent',
        ]);

        return redirect()
            ->route("{$cfg->route}.index")
            ->with('success', "Absensi {$siswa->nama} berhasil dicatat.");
    }
}
