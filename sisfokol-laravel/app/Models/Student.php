<?php

namespace App\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $table = 'siswa';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Sync English to Indonesian
            if (isset($model->name)) $model->nama = $model->name;
            if (isset($model->gender)) $model->jenis_kelamin = $model->gender;
            if (isset($model->birth_place)) $model->tempat_lahir = $model->birth_place;
            if (isset($model->birth_date)) $model->tanggal_lahir = $model->birth_date;
            if (isset($model->address)) $model->alamat = $model->address;
            if (isset($model->phone)) $model->telepon = $model->phone;
            if (isset($model->photo_path)) $model->foto = $model->photo_path;
            if (isset($model->qrcode_path)) $model->qrcode = $model->qrcode_path;

            // Sync Indonesian to English
            if (isset($model->nama)) $model->name = $model->nama;
            if (isset($model->jenis_kelamin)) $model->gender = $model->jenis_kelamin;
            if (isset($model->tempat_lahir)) $model->birth_place = $model->tempat_lahir;
            if (isset($model->tanggal_lahir)) $model->birth_date = $model->tanggal_lahir;
            if (isset($model->alamat)) $model->address = $model->alamat;
            if (isset($model->telepon)) $model->phone = $model->telepon;
            if (isset($model->foto)) $model->photo_path = $model->foto;
            if (isset($model->qrcode)) $model->qrcode_path = $model->qrcode;
        });
    }

    protected $fillable = [
        'user_id',
        'academic_year_id',
        'classroom_id',
        'nis',
        'nisn',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'phone',
        'email',
        'father_name',
        'mother_name',
        'parent_phone',
        'photo_path',
        'qrcode_path',
        'is_active',
        'legacy_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function extracurriculars(): BelongsToMany
    {
        return $this->belongsToMany(Extracurricular::class, 'student_extracurricular')
            ->withPivot('academic_year_id', 'score', 'description')
            ->withTimestamps();
    }
}
