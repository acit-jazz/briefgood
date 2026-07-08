<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\AiAnalysisStatus;
use App\Enums\BriefStatus;
use Database\Factories\BriefFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brief extends Model
{
    /** @use HasFactory<BriefFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'created_by',
        'client_name',
        'industry',
        'title',
        'brief_date',
        'budget',
        'deadline',
        'notes',
        'status',
        'ai_status',
        'ai_model',
        'ai_error',
        'latest_analysis_id',
        'primary_file_name',
        'primary_file_path',
        'primary_file_mime',
        'primary_file_size',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'brief_date' => 'date',
            'deadline' => 'date',
            'budget' => 'decimal:2',
            'metadata' => 'array',
            'status' => BriefStatus::class,
            'ai_status' => AiAnalysisStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(BriefFile::class);
    }

    public function aiAnalysisResults(): HasMany
    {
        return $this->hasMany(AiAnalysisResult::class);
    }

    public function latestAnalysis(): BelongsTo
    {
        return $this->belongsTo(AiAnalysisResult::class, 'latest_analysis_id');
    }

    public function pitchAssignments(): HasMany
    {
        return $this->hasMany(PitchAssignment::class);
    }

    public function resourceAllocations(): HasMany
    {
        return $this->hasMany(ResourceAllocation::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['ai_status'] ?? null, fn (Builder $q, string $status) => $q->where('ai_status', $status));
    }
}
