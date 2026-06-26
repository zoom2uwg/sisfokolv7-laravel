<?php

namespace App\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $table = 'tahun_ajaran';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Sync English to Indonesian
            if (isset($model->name)) $model->nama = $model->name;
            if (isset($model->start_date)) $model->tanggal_mulai = $model->start_date;
            if (isset($model->end_date)) $model->tanggal_selesai = $model->end_date;
            if (isset($model->is_active)) $model->aktif = $model->is_active;

            // Sync Indonesian to English
            if (isset($model->nama)) $model->name = $model->nama;
            if (isset($model->tanggal_mulai)) $model->start_date = $model->tanggal_mulai;
            if (isset($model->tanggal_selesai)) $model->end_date = $model->tanggal_selesai;
            if (isset($model->aktif)) $model->is_active = $model->aktif;
        });
    }

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
