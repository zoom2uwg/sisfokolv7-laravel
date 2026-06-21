<?php

namespace App\Plugins\Kurikulum\Subscribers;

use App\Modules\Evaluation\Events\EvaluationResolveFramework;
use App\Plugins\Kurikulum\Models\{Kurikulum, StrukturKurikulum, KomponenKompetensi};
use App\Support\{TenantContext, PluginRegistry};

class EvaluationFrameworkSubscriber
{
    public function handleEvaluationResolveFramework(EvaluationResolveFramework $event): void
    {
        $tenantId = app(TenantContext::class)->id;
        
        // Only run if active for tenant
        if ($tenantId && !app(PluginRegistry::class)->isActiveForTenant('kurikulum', $tenantId)) {
            return;
        }

        $mapel = $event->mapel;
        $kelas = $event->kelas;

        if (! $mapel->kurikulum_id) {
            return;
        }

        $kurikulum = Kurikulum::find($mapel->kurikulum_id);
        if (! $kurikulum) {
            return;
        }

        // Find structure matching jenjang/kelas
        $strukturQuery = StrukturKurikulum::where('kurikulum_id', $kurikulum->id);
        if ($kelas) {
            $strukturQuery->where('jenjang', $kelas->jenjang())
                          ->where('kelas', (string) $kelas->tingkat);
        }
        
        $struktur = $strukturQuery->first();
        if (! $struktur) {
            return;
        }

        $komponenKis = KomponenKompetensi::where('struktur_id', $struktur->id)
            ->pluck('kode_kompetensi')
            ->all();

        $event->framework = [
            'kurikulum'   => $kurikulum->nama_kurikulum,
            'ki'          => $komponenKis,
            'fase'        => $struktur->fase,
            'pedagogis'   => $struktur->komponenKompetensi()->first()?->pendekatan_pedagogis ?? 'konvensional',
        ];
    }

    public function subscribe($events): array
    {
        return [
            EvaluationResolveFramework::class => static::class . '@handleEvaluationResolveFramework',
        ];
    }
}
