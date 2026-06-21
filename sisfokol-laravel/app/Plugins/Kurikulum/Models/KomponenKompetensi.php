<?php

namespace App\Plugins\Kurikulum\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomponenKompetensi extends Model
{
    use BelongsToTenant, TracksAuditColumns;

    protected $table = 'komponen_kompetensi';

    protected $fillable = ['struktur_id', 'kode_kompetensi', 'teks_kompetensi', 'pendekatan_pedagogis'];

    public function struktur(): BelongsTo
    {
        return $this->belongsTo(StrukturKurikulum::class, 'struktur_id');
    }
}
