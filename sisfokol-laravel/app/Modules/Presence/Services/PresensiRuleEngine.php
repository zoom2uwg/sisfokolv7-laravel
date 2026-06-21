<?php

namespace App\Modules\Presence\Services;

use App\Models\AttendanceTime;
use Carbon\Carbon;

class PresensiRuleEngine
{
    /**
     * Determine presence status (present, late, early) based on check-in/out time.
     * $time: check-in time (e.g. '07:05:00' or Carbon instance)
     * $type: 'in' or 'out'
     */
    public function evaluate(Carbon $time, string $type): string
    {
        // Try to find active AttendanceTime for this type
        $rule = AttendanceTime::where('type', $type)
            ->where('is_active', true)
            ->first();

        $timeStr = $time->format('H:i:s');

        if ($type === 'in') {
            $start = $rule ? Carbon::parse($rule->start_time)->format('H:i:s') : '06:00:00';
            $end = $rule ? Carbon::parse($rule->end_time)->format('H:i:s') : '07:15:00';

            if ($timeStr < $start) {
                return 'present'; // checked in early, count as present
            }
            if ($timeStr <= $end) {
                return 'present';
            }
            return 'late';
        } else {
            // 'out'
            $start = $rule ? Carbon::parse($rule->start_time)->format('H:i:s') : '14:00:00';
            $end = $rule ? Carbon::parse($rule->end_time)->format('H:i:s') : '17:00:00';

            if ($timeStr < $start) {
                return 'early'; // checked out before start time
            }
            return 'present';
        }
    }
}
