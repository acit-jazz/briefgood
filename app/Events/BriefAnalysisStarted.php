<?php

namespace App\Events;

use App\Models\Brief;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BriefAnalysisStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Brief $brief) {}
}
