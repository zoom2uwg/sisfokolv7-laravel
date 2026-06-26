<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Siswa;
use App\Support\Crudlfix\Crudlfix;
use Illuminate\Validation\Rule;

/**
 * SiswaController — refactored with CRUDLFIX.
 * Uses policy-based authorization (SiswaPolicy).
 */
class SiswaController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        $tenantId = auth()->user()->tenant_id;

        return [
            'model'     => Siswa::class,
            'view'      => 'academic.siswa',
            'route'     => 'academic.siswa',
            'authorize' => 'siswa',
            'authType'  => 'policy',
            'search'    => ['nama', 'nis', 'nisn'],
            'rules'     => [
                'store' => [
                    'nis'             => ['required', 'string', 'max:30', Rule::unique('siswa')->where('tenant_id', $tenantId)],
                    'nisn'            => ['nullable', 'string', 'max:30', Rule::unique('siswa')->where('tenant_id', $tenantId)],
                    'nama'            => ['required', 'string', 'max:100'],
                    'jenis_kelamin'   => ['required', 'in:L,P'],
                    'tempat_lahir'    => ['nullable', 'string', 'max:50'],
                    'tanggal_lahir'   => ['nullable', 'date'],
                    'alamat'          => ['nullable', 'string'],
                    'telepon'         => ['nullable', 'string', 'max:20'],
                    'agama'           => ['nullable', 'string', 'max:20'],
                    'status'          => ['required', 'in:aktif,nonaktif,lulus,pindah,keluar'],
                ],
                'update' => [
                    'nis'             => ['required', 'string', 'max:30'],
                    'nisn'            => ['nullable', 'string', 'max:30'],
                    'nama'            => ['required', 'string', 'max:100'],
                    'jenis_kelamin'   => ['required', 'in:L,P'],
                    'tempat_lahir'    => ['nullable', 'string', 'max:50'],
                    'tanggal_lahir'   => ['nullable', 'date'],
                    'alamat'          => ['nullable', 'string'],
                    'telepon'         => ['nullable', 'string', 'max:20'],
                    'agama'           => ['nullable', 'string', 'max:20'],
                    'status'          => ['required', 'in:aktif,nonaktif,lulus,pindah,keluar'],
                ],
            ],
            'perPage' => 15,
            'varName' => 'siswa',  // View uses $siswa, not $siswas
        ];
    }
}
