<?php

namespace App\Modules\Presence\Observers;

use App\Models\Attendance;
use App\Modules\Auth\Services\AuditLogger;

class AttendanceObserver
{
    public function created(Attendance $attendance): void
    {
        app(AuditLogger::class)->log(
            'presence.recorded',
            null,
            [
                'user_id' => $attendance->user_id,
                'date' => $attendance->date?->format('Y-m-d'),
                'time' => $attendance->time?->format('H:i'),
                'type' => $attendance->type,
                'status' => $attendance->status,
            ],
            request(),
            [],
            Attendance::class,
            $attendance->id
        );
    }
}
