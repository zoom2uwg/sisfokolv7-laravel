<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mapel extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected static function newFactory()
    {
        return \Database\Factories\MapelFactory::new();
    }

    protected $table = 'mapel';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Sync Indonesian to English
            if (isset($model->kode)) $model->code = $model->kode;
            if (isset($model->nama)) $model->name = $model->nama;
            if (isset($model->mapel_jenis_id)) $model->subject_type_id = $model->mapel_jenis_id;

            // Sync English to Indonesian
            if (isset($model->code)) $model->kode = $model->code;
            if (isset($model->name)) $model->nama = $model->name;
            if (isset($model->subject_type_id)) $model->mapel_jenis_id = $model->subject_type_id;
        });
    }

    protected $fillable = [
        'kode', 'nama', 'mapel_jenis_id', 'kkm', 'kurikulum_id', 'jenjang',
    ];

    protected function casts(): array
    {
        return [
            'kkm' => 'decimal:2',
            'mapel_jenis_id' => 'integer',
            'kurikulum_id' => 'integer',
        ];
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(MapelJenis::class, 'mapel_jenis_id');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'mapel_id');
    }
}
