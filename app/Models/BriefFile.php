<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BriefFile extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'brief_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'disk',
    ];

    public function brief(): BelongsTo
    {
        return $this->belongsTo(Brief::class);
    }
}
