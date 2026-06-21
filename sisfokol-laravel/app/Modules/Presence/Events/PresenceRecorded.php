<?php

namespace App\Modules\Presence\Events;

use App\Models\Attendance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PresenceRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public Attendance $attendance) {}
}
