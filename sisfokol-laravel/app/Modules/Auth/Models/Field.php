<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    protected $fillable = ['kode', 'model', 'kolom', 'label', 'kategori', 'default_visibility'];

    public function roleOverrides(): HasMany
    {
        return $this->hasMany(FieldRoleOverride::class);
    }
}
