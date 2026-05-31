<?php

namespace Database\Factories;

use App\Enums\AiAnalysisStatus;
use App\Enums\BriefStatus;
use App\Models\Brief;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brief>
 */
class BriefFactory extends Factory
{
    protected $model = Brief::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'client_name' => fake()->company(),
            'industry' => fake()->randomElement(['Fintech', 'FMCG', 'Technology', 'Healthcare']),
            'title' => fake()->sentence(4),
            'brief_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'budget' => fake()->randomFloat(2, 5000, 500000),
            'deadline' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'notes' => fake()->optional()->paragraph(),
            'status' => BriefStatus::New,
            'ai_status' => AiAnalysisStatus::Pending,
        ];
    }
}
