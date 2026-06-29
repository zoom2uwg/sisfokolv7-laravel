<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Menu extends Model
{
    protected $fillable = [
        'tenant_id', 'kode', 'label', 'icon', 'route', 'urutan',
        'parent_id', 'group', 'permission_required', 'plugin_kode',
        'is_system', 'is_platform', 'aktif',
    ];

    protected function casts(): array
    {
        return ['is_system' => 'boolean', 'is_platform' => 'boolean', 'aktif' => 'boolean'];
    }

    public function parent(): BelongsTo { return $this->belongsTo(Menu::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(Menu::class, 'parent_id')->orderBy('urutan'); }
    public function roleOverrides(): HasMany { return $this->hasMany(MenuRoleOverride::class); }
}
