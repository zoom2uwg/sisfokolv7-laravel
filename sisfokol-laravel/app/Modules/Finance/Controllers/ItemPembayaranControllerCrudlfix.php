<?php

namespace App\Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\TahunAjaran;
use App\Modules\Finance\Models\ItemPembayaran;
use App\Modules\Finance\Requests\StoreItemPembayaranRequest;
use App\Support\Crudlfix\Crudlfix;

/**
 * ItemPembayaranController — refactored to use CRUDLFIX.
 *
 * Before: 89 lines.
 * After:  ~35 lines.
 */
class ItemPembayaranController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => ItemPembayaran::class,
            'view'       => 'finance.item-pembayaran',
            'route'      => 'finance.item-pembayaran',
            'authorize'  => 'item-pembayaran',
            'search'     => ['nama', 'jenis'],
            'with'       => ['tahunAjaran'],
            'requestClass' => StoreItemPembayaranRequest::class,
            'perPage'    => 15,
            'viewData'   => [
                'tahunAjaran' => TahunAjaran::where('aktif', true)->get(),
            ],
        ];
    }

    /**
     * Hook: merge checkbox boolean before store.
     */
    protected function beforeStore(array $validated, $request): array
    {
        $validated['aktif'] = $request->has('aktif');
        return $validated;
    }

    /**
     * Hook: merge checkbox boolean before update.
     */
    protected function beforeUpdate(array $validated, $model, $request): array
    {
        $validated['aktif'] = $request->has('aktif');
        return $validated;
    }
}
