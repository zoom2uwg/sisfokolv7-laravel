<?php

namespace App\Modules\Evaluation\Events;

use App\Modules\Academic\Models\Siswa;
use App\Modules\Academic\Models\TahunAjaran;

class RaportRenderSection
{
    /** @var array<string,string> section_name => html */
    public array $sections = [];

    public function __construct(
        public readonly Siswa $siswa,
        public readonly TahunAjaran $tapel,
        public readonly int $semester,
    ) {}
}
