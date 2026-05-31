<?php

namespace App\Events;

use App\Models\Brief;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BriefAnalysisFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Brief $brief,
        public \Throwable $exception,
    ) {}
}
