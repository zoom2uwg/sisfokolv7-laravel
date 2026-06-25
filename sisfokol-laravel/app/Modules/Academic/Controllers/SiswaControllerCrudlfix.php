<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Academic\Requests\StoreSiswaRequest;
use App\Modules\Academic\Requests\UpdateSiswaRequest;
use App\Support\Crudlfix\Crudlfix;

/**
 * SiswaController — refactored to use CRUDLFIX.
 *
 * Before: 84 lines of boilerplate CRUD code.
 * After:  ~30 lines of configuration + 2 lifecycle hooks.
 *
 * All standard CRUD operations (index, create, store, show, edit, update, destroy)
 * are handled automatically by the Crudlfix trait.
 */
class SiswaController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'     => Siswa::class,
            'view'      => 'academic.siswa',
            'route'     => 'academic.siswa',
            'authorize' => 'siswa',           // Gate::authorize('siswa.view'), etc.
            'search'    => ['nama', 'nis', 'nisn'],
            'with'      => ['orangTuas'],     // Eager load for index
            'rules'     => [
                'store'  => (new StoreSiswaRequest())->rules(),
                'update' => (new UpdateSiswaRequest())->rules(),
            ],
            'perPage'   => 15,
        ];
    }

    /**
     * Hook: augment data before storing.
     * Optional — remove if not needed.
     */
    protected function beforeStore(array $validated): array
    {
        // Example: $validated['created_by'] = auth()->id();
        return $validated;
    }
}
