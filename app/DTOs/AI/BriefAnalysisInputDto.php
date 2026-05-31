<?php

namespace App\DTOs\AI;

readonly class BriefAnalysisInputDto
{
    /**
     * @param  list<array{name: string, path: string}>  $attachments
     * @param  list<array{id: string, name: string, category: string, services: list<string>}>  $businessUnits
     */
    public function __construct(
        public string $briefId,
        public string $title,
        public string $clientName,
        public ?string $industry,
        public ?string $notes,
        public string $briefText,
        public array $attachments = [],
        public array $businessUnits = [],
    ) {}
}
