<?php

namespace App\Plugins\Kurikulum\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrukturKurikulum extends Model
{
    use BelongsToTenant, TracksAuditColumns;

    protected $table = 'struktur_kurikulum';

    protected $fillable = ['kurikulum_id', 'jenjang', 'kelas', 'fase', 'jenis_kegiatan'];

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function komponenKompetensi(): HasMany
    {
        return $this->hasMany(KomponenKompetensi::class, 'struktur_id');
    }
}
