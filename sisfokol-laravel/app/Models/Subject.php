<?php

namespace App\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $table = 'mapel';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Sync English to Indonesian
            if (isset($model->code)) $model->kode = $model->code;
            if (isset($model->name)) $model->nama = $model->name;
            if (isset($model->subject_type_id)) $model->mapel_jenis_id = $model->subject_type_id;

            // Sync Indonesian to English
            if (isset($model->kode)) $model->code = $model->kode;
            if (isset($model->nama)) $model->name = $model->nama;
            if (isset($model->mapel_jenis_id)) $model->subject_type_id = $model->mapel_jenis_id;
        });
    }

    protected $fillable = [
        'academic_year_id',
        'subject_type_id',
        'code',
        'name',
        'description',
        'is_exam',
        'phase',
        'legacy_id',
    ];

    protected function casts(): array
    {
        return [
            'is_exam' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function subjectType(): BelongsTo
    {
        return $this->belongsTo(SubjectType::class);
    }

    public function descriptions(): HasMany
    {
        return $this->hasMany(SubjectDescription::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_subject')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }
}
