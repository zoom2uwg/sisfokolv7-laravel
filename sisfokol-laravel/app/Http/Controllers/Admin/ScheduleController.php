<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Models\Classroom;
use App\Models\Day;
use App\Models\Employee;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TimeSlot;
use App\Modules\Academic\Models\TahunAjaran;
use App\Support\Crudlfix\Crudlfix;

class ScheduleController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'        => Schedule::class,
            'view'         => 'admin.schedules',
            'route'        => 'admin.schedules',
            'requestClass' => StoreScheduleRequest::class,
            'with'         => ['academicYear', 'classroom', 'subject', 'teacher', 'room', 'day', 'timeSlot'],
            'perPage'      => 30,
            'viewData'     => [
                'academicYears' => TahunAjaran::orderBy('nama', 'desc')->get(),
                'classrooms'    => Classroom::orderBy('name')->get(),
                'subjects'      => Subject::orderBy('name')->get(),
                'teachers'      => Employee::where('position', 'guru')->get(),
                'rooms'         => Room::orderBy('name')->get(),
                'days'          => Day::orderBy('id')->get(),
                'timeSlots'     => TimeSlot::orderBy('order')->get(),
            ],
        ];
    }
}
