<?php

namespace App\Plugins\Kurikulum\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Kurikulum\Models\{Kurikulum, StrukturKurikulum};
use App\Support\Crudlfix\Crudlfix;

/**
 * StrukturKurikulumController — CRUDLFIX refactored (96 lines → 50 lines)
 * 
 * Features: search, filter by jenjang & kurikulum, sort, export
 * Bug fix: jenis_kegiatan enum changed to kokurikuler_p5
 */
class StrukturKurikulumController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'     => StrukturKurikulum::class,
            'view'      => 'kurikulum::struktur',
            'route'     => 'kurikulum.struktur',
            'authorize' => 'kurikulum',
            'authType'  => 'policy',
            'with'      => ['kurikulum'],
            'search'    => ['jenjang', 'kelas', 'fase'],
            'filters'   => [
                'jenjang' => ['column' => 'jenjang', 'operator' => '='],
                'kurikulum_id' => ['column' => 'kurikulum_id', 'operator' => '='],
            ],
            'rules'     => [
                'store' => [
                    'kurikulum_id'   => 'required|exists:kurikulum,id',
                    'jenjang'        => 'required|in:SD,SMP,SMA,SMK',
                    'kelas'          => 'required|string|max:5',
                    'fase'           => 'nullable|in:A,B,C,D,E,F',
                    'jenis_kegiatan' => 'required|in:intrakurikuler,kokurikuler_p5,ekstrakurikuler',
                ],
                'update' => [
                    'kurikulum_id'   => 'required|exists:kurikulum,id',
                    'jenjang'        => 'required|in:SD,SMP,SMA,SMK',
                    'kelas'          => 'required|string|max:5',
                    'fase'           => 'nullable|in:A,B,C,D,E,F',
                    'jenis_kegiatan' => 'required|in:intrakurikuler,kokurikuler_p5,ekstrakurikuler',
                ],
            ],
            'viewData'  => [
                'kurikulumOptions' => Kurikulum::where('status_aktif', true)
                    ->orderBy('nama_kurikulum')
                    ->pluck('nama_kurikulum', 'id'),
            ],
            'defaultSort' => 'jenjang',
            'defaultDir'  => 'asc',
            'perPage'     => 15,
        ];
    }
}
