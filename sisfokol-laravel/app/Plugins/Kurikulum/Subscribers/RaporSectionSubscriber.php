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

        // Generate capaian kompetensi section HTML based on siswa's nilai + kurikulum
        $html = '<p><em>Section Capaian Kompetensi dari plugin Kurikulum.</em></p>';
        $event->sections['Capaian Kompetensi'] = $html;
    }

    public function subscribe($events): array
    {
        return [
            RaportRenderSection::class => 'handleRaportRenderSection',
        ];
    }
}
