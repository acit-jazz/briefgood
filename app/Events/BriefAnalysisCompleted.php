<?php

namespace App\Events;

use App\Models\AiAnalysisResult;
use App\Models\Brief;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BriefAnalysisCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Brief $brief,
        public AiAnalysisResult $analysis,
    ) {}
}
