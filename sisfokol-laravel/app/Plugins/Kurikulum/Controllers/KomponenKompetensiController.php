<?php

namespace App\Plugins\Kurikulum\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Kurikulum\Models\{Kurikulum, StrukturKurikulum, KomponenKompetensi};
use App\Support\Crudlfix\Crudlfix;

/**
 * KomponenKompetensiController — CRUDLFIX refactored (97 lines → 45 lines)
 * 
 * Features: search, nested eager loading, sort, export
 */
class KomponenKompetensiController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'     => KomponenKompetensi::class,
            'view'      => 'kurikulum::komponen',
            'route'     => 'kurikulum.komponen',
            'authorize' => 'kurikulum',
            'authType'  => 'policy',
            'with'      => ['struktur.kurikulum'],
            'search'    => ['kode_kompetensi', 'teks_kompetensi'],
            'rules'     => [
                'store' => [
                    'struktur_id'          => 'required|exists:struktur_kurikulum,id',
                    'kode_kompetensi'      => 'required|string|max:30',
                    'teks_kompetensi'      => 'required|string|max:1000',
                    'pendekatan_pedagogis' => 'nullable|string|max:50',
                ],
                'update' => [
                    'struktur_id'          => 'required|exists:struktur_kurikulum,id',
                    'kode_kompetensi'      => 'required|string|max:30',
                    'teks_kompetensi'      => 'required|string|max:1000',
                    'pendekatan_pedagogis' => 'nullable|string|max:50',
                ],
            ],
            'viewData'  => [
                'strukturOptions' => StrukturKurikulum::with('kurikulum')
                    ->get()
                    ->mapWithKeys(fn($s) => [
                        $s->id => "{$s->kurikulum->nama_kurikulum} — {$s->jenjang} Kelas {$s->kelas}" . ($s->fase ? " (Fase {$s->fase})" : ''),
                    ]),
            ],
            'defaultSort' => 'kode_kompetensi',
            'defaultDir'  => 'asc',
            'perPage'     => 20,
        ];
    }
}
