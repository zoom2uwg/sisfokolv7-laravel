<?php

namespace App\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Absence extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $fillable = [
        'user_id',
        'absentable_type',
        'absentable_id',
        'date',
        'type',
        'reason',
        'attachment_path',
        'legacy_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function absentable(): MorphTo
    {
        return $this->morphTo();
    }
}
