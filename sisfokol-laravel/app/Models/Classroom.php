<?php

namespace App\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $table = 'kelas';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Sync English to Indonesian
            if (isset($model->name)) $model->nama = $model->name;
            if (isset($model->capacity)) $model->kapasitas = $model->capacity;

            // Sync Indonesian to English
            if (isset($model->nama)) $model->name = $model->nama;
            if (isset($model->kapasitas)) $model->capacity = $model->kapasitas;
        });
    }

    protected $fillable = [
        'academic_year_id',
        'name',
        'level',
        'major',
        'capacity',
        'homeroom_teacher_id',
        'description',
        'legacy_id',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'homeroom_teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
