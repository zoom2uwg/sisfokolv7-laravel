<?php

namespace App\Modules\Auth\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class MenuRoleOverride extends Model
{
    use BelongsToTenant;

    protected $fillable = ['menu_id', 'role_id', 'tenant_id', 'visible'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
