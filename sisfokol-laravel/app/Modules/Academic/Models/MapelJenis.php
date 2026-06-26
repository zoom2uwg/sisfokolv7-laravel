<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MapelJenis extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $table = 'mapel_jenis';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Sync Indonesian to English
            if (isset($model->kode)) $model->code = $model->kode;
            if (isset($model->nama)) $model->name = $model->nama;

            // Sync English to Indonesian
            if (isset($model->code)) $model->kode = $model->code;
            if (isset($model->name)) $model->nama = $model->name;
        });
    }

    protected $fillable = ['nama', 'kode'];

    public function mapels(): HasMany
    {
        return $this->hasMany(Mapel::class, 'mapel_jenis_id');
    }
}
