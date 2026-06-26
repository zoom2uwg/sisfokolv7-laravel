<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Models\Subject;
use App\Models\SubjectType;
use App\Modules\Academic\Models\TahunAjaran;
use App\Support\Crudlfix\Crudlfix;

class SubjectController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'        => Subject::class,
            'view'         => 'admin.subjects',
            'route'        => 'admin.subjects',
            'requestClass' => StoreSubjectRequest::class,
            'search'       => ['code', 'name'],
            'with'         => ['subjectType', 'academicYear'],
            'perPage'      => 20,
            'viewData'     => [
                'academicYears' => TahunAjaran::orderBy('nama', 'desc')->get(),
                'subjectTypes'  => SubjectType::orderBy('name')->get(),
            ],
        ];
    }
}
