<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassroomRequest;
use App\Models\Classroom;
use App\Models\Employee;
use App\Modules\Academic\Models\TahunAjaran;
use App\Support\Crudlfix\Crudlfix;

class ClassroomController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'        => Classroom::class,
            'view'         => 'admin.classrooms',
            'route'        => 'admin.classrooms',
            'requestClass' => StoreClassroomRequest::class,
            'search'       => ['name', 'level', 'major'],
            'with'         => ['homeroomTeacher', 'academicYear'],
            'perPage'      => 20,
            'viewData'     => [
                'teachers'      => Employee::where('position', 'guru')->get(),
                'academicYears' => TahunAjaran::orderBy('nama', 'desc')->get(),
            ],
        ];
    }
}
