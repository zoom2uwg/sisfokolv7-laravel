<?php

namespace App\Plugins\Kurikulum\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kurikulum extends Model
{
    use SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $table = 'kurikulum';

    protected $fillable = ['kurikulum_id', 'nama_kurikulum', 'status_aktif'];

    protected function casts(): array
    {
        return ['status_aktif' => 'boolean'];
    }

    public function strukturKurikulum(): HasMany
    {
        return $this->hasMany(StrukturKurikulum::class, 'kurikulum_id');
    }
}
