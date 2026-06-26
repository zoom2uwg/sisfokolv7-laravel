<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TahunAjaran extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected static function newFactory()
    {
        return \Database\Factories\TahunAjaranFactory::new();
    }

    protected $table = 'tahun_ajaran';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Sync Indonesian to English
            if (isset($model->nama)) $model->name = $model->nama;
            if (isset($model->tanggal_mulai)) $model->start_date = $model->tanggal_mulai;
            if (isset($model->tanggal_selesai)) $model->end_date = $model->tanggal_selesai;
            if (isset($model->aktif)) $model->is_active = $model->aktif;

            // Sync English to Indonesian
            if (isset($model->name)) $model->nama = $model->name;
            if (isset($model->start_date)) $model->tanggal_mulai = $model->start_date;
            if (isset($model->end_date)) $model->tanggal_selesai = $model->end_date;
            if (isset($model->is_active)) $model->aktif = $model->is_active;
        });
    }

    protected $fillable = [
        'nama', 'tanggal_mulai', 'tanggal_selesai', 'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class, 'tahun_ajaran_id');
    }

    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'tahun_ajaran_id');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'tahun_ajaran_id');
    }
}
