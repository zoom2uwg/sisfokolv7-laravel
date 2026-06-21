<?php

namespace App\Modules\Evaluation\Events;

use App\Modules\Academic\Models\{Kelas, Mapel};

/**
 * ADR-009: Core fires this; Kurikulum plugin listens and returns framework metadata (KI/fase/pedagogis).
 * Without plugin active, controller falls back to generic.
 */
class EvaluationResolveFramework
{
    public ?array $framework = null;  // Filled by subscriber

    public function __construct(
        public readonly Mapel $mapel,
        public readonly ?Kelas $kelas = null,
    ) {}
}
