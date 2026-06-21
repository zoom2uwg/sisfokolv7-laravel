<?php

namespace App\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $fillable = [
        'user_id',
        'attendable_type',
        'attendable_id',
        'date',
        'time',
        'type',
        'source',
        'status',
        'ip_address',
        'latitude',
        'longitude',
        'note',
        'legacy_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'datetime:H:i',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }
}
