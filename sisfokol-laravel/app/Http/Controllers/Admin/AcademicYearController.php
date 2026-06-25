<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Models\AcademicYear;
use App\Support\Crudlfix\Crudlfix;

class AcademicYearController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'        => AcademicYear::class,
            'view'         => 'admin.academic-years',
            'route'        => 'admin.academic-years',
            'requestClass' => StoreAcademicYearRequest::class,
            'perPage'      => 20,
        ];
    }
}
