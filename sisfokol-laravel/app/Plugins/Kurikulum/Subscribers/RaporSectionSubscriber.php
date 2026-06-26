<?php

namespace App\Plugins\Kurikulum\Subscribers;

use App\Modules\Evaluation\Events\RaportRenderSection;
use App\Support\{TenantContext, PluginRegistry};

class RaporSectionSubscriber
{
    public function handleRaportRenderSection(RaportRenderSection $event): void
    {
        $tenantId = app(TenantContext::class)->id;
        
        // Only run if active for tenant
        if ($tenantId && !app(PluginRegistry::class)->isActiveForTenant('kurikulum', $tenantId)) {
            return;
        }

        // TODO: Implementasi query kurikulum untuk capaian kompetensi
        // 1. Resolve siswa → kelas → jenjang dari $event->siswa
        // 2. Query Kurikulum yang aktif (status_aktif = true)
        // 3. Query StrukturKurikulum by kurikulum_id + jenjang + kelas
        // 4. Query KomponenKompetensi by struktur_id
        // 5. Render teks_kompetensi grouped by kode_kompetensi
        // NOTE: Event RaportRenderSection belum di-dispatch oleh RaporGeneratorService
        $html = '<p><em>Section Capaian Kompetensi dari plugin Kurikulum.</em></p>';
        $event->sections['Capaian Kompetensi'] = $html;
    }

    public function subscribe($events): array
    {
        return [
            RaportRenderSection::class => [static::class, 'handleRaportRenderSection'],
        ];
    }
}
