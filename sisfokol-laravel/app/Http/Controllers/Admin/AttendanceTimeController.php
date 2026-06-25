<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceTimeRequest;
use App\Models\AttendanceTime;
use App\Support\Crudlfix\Crudlfix;

class AttendanceTimeController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'        => AttendanceTime::class,
            'view'         => 'admin.attendance-times',
            'route'        => 'admin.attendance-times',
            'requestClass' => StoreAttendanceTimeRequest::class,
            'search'       => ['name'],
            'with'         => ['academicYear'],
            'perPage'      => 20,
        ];
    }
}
