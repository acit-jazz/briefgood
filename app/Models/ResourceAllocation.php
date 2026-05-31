<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAllocation extends Model
{
    use HasUuid;

    protected $fillable = [
        'brief_id',
        'resource_id',
        'estimated_hours',
        'estimated_workload_percent',
        'estimated_duration_days',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'estimated_hours' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function brief(): BelongsTo
    {
        return $this->belongsTo(Brief::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
