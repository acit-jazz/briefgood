<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\PitchAssignmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PitchAssignment extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'brief_id',
        'business_unit_id',
        'assigned_by',
        'pic_user_id',
        'confidence',
        'status',
        'rejection_reason',
        'internal_notes',
        'responded_at',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:2',
            'status' => PitchAssignmentStatus::class,
            'responded_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function brief(): BelongsTo
    {
        return $this->belongsTo(Brief::class);
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PitchResponse::class);
    }
}
