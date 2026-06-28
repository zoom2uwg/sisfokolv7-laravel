<?php

namespace App\Plugins\Kurikulum\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Kurikulum\Models\Kurikulum;
use App\Support\Crudlfix\Crudlfix;

/**
 * KurikulumController — CRUDLFIX refactored (86 lines → 36 lines)
 * 
 * Features: search, filter by status_aktif, sort, export CSV
 */
class KurikulumController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'     => Kurikulum::class,
            'view'      => 'kurikulum::kurikulum',
            'route'     => 'kurikulum',
            'authorize' => 'kurikulum',
            'authType'  => 'policy',
            'varName'   => 'kurikulumList',
            'search'    => ['kurikulum_id', 'nama_kurikulum', 'deskripsi'],
            'filters'   => [
                'status_aktif' => ['column' => 'status_aktif', 'operator' => '='],
            ],
            'rules'     => [
                'store' => [
                    'kurikulum_id'   => 'required|string|max:20|unique:kurikulum,kurikulum_id',
                    'nama_kurikulum' => 'required|string|max:100',
                    'deskripsi'      => 'nullable|string|max:500',
                    'status_aktif'   => 'boolean',
                ],
                'update' => [
                    'kurikulum_id'   => 'required|string|max:20|unique:kurikulum,kurikulum_id,{{id}}',
                    'nama_kurikulum' => 'required|string|max:100',
                    'deskripsi'      => 'nullable|string|max:500',
                    'status_aktif'   => 'boolean',
                ],
            ],
            'defaultSort' => 'nama_kurikulum',
            'defaultDir'  => 'asc',
            'perPage'     => 15,
        ];
    }
}
