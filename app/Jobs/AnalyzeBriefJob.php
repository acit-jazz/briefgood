<?php

namespace App\Jobs;

use App\Models\Brief;
use App\Services\AI\AIAnalysisPipeline;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeBriefJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public function __construct(
        public Brief $brief,
        public bool $useAdvancedModel = false,
        public ?string $aiProvider = null,
    ) {
        $this->tries = (int) config('briefgood.ai.max_retries', 2);
        $this->onQueue('ai-analysis');
    }

    public function handle(AIAnalysisPipeline $pipeline): void
    {
        $pipeline->run($this->brief, $this->useAdvancedModel, $this->aiProvider);
    }
}
