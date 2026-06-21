<?php

namespace App\Modules\Evaluation\Services;

use App\Modules\Academic\Models\{Kelas, Mapel};
use App\Modules\Evaluation\Events\EvaluationResolveFramework;

class EvaluationFrameworkResolver
{
    /**
     * Fire EvaluationResolveFramework event. If Kurikulum plugin active & listening,
     * it will fill $event->framework with {ki: [...], fase, pedagogis}.
     * Returns null when no plugin responds → controller renders generic.
     */
    public function resolve(Mapel $mapel, ?Kelas $kelas = null): ?array
    {
        $event = new EvaluationResolveFramework($mapel, $kelas);
        event($event);
        return $event->framework;
    }
}
