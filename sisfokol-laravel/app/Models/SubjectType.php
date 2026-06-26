<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mapel_jenis';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Sync English to Indonesian
            if (isset($model->code)) $model->kode = $model->code;
            if (isset($model->name)) $model->nama = $model->name;

            // Sync Indonesian to English
            if (isset($model->kode)) $model->code = $model->kode;
            if (isset($model->nama)) $model->name = $model->nama;
        });
    }

    protected $fillable = [
        'code',
        'name',
        'description',
    ];
}
