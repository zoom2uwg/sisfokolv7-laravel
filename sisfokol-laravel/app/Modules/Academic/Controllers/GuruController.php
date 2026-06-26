<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Guru;
use App\Support\Crudlfix\Crudlfix;

/**
 * GuruController — created with CRUDLFIX in ~25 lines.
 *
 * Full CRUD: index (search, paginate), create, store, show, edit, update, destroy.
 * Authorization: Gate::authorize('guru.view'), etc.
 */
class GuruController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'     => Guru::class,
            'view'      => 'academic.guru',
            'route'     => 'academic.guru',
            'authorize' => 'guru',
            'authType'  => 'policy',  // Uses GuruPolicy
            'search'    => ['nama', 'nip', 'email'],
            'rules'     => [
                'store' => [
                    'nip'           => 'required|string|max:30|unique:guru,nip,NULL,id,tenant_id,' . auth()->user()->tenant_id,
                    'nama'          => 'required|string|max:100',
                    'jenis_kelamin' => 'required|in:L,P',
                    'telepon'       => 'nullable|string|max:30',
                    'email'         => 'nullable|email|max:100',
                    'jabatan'       => 'nullable|string|max:100',
                    'aktif'         => 'boolean',
                ],
                'update' => [
                    'nip'           => 'required|string|max:30|unique:guru,nip,{{id}},id,tenant_id,' . auth()->user()->tenant_id,
                    'nama'          => 'required|string|max:100',
                    'jenis_kelamin' => 'required|in:L,P',
                    'telepon'       => 'nullable|string|max:30',
                    'email'         => 'nullable|email|max:100',
                    'jabatan'       => 'nullable|string|max:100',
                    'aktif'         => 'boolean',
                ],
            ],
            'perPage'   => 15,
        ];
    }
}
