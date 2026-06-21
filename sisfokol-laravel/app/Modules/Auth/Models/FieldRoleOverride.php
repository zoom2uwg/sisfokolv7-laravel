<?php

namespace App\Modules\Auth\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class FieldRoleOverride extends Model
{
    use BelongsToTenant;

    protected $fillable = ['field_id', 'role_id', 'tenant_id', 'visibility'];

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}
