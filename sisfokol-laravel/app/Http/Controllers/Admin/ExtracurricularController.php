<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExtracurricularRequest;
use App\Models\Employee;
use App\Models\Extracurricular;
use App\Support\Crudlfix\Crudlfix;

class ExtracurricularController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'        => Extracurricular::class,
            'view'         => 'admin.extracurriculars',
            'route'        => 'admin.extracurriculars',
            'requestClass' => StoreExtracurricularRequest::class,
            'search'       => ['name', 'description'],
            'with'         => ['coach'],
            'perPage'      => 20,
            'viewData'     => [
                'coaches' => Employee::where('position', 'guru')->get(),
            ],
        ];
    }
}
