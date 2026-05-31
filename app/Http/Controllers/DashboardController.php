<?php

namespace App\Http\Controllers;

use App\Enums\BriefStatus;
use App\Enums\PitchAssignmentStatus;
use App\Models\Brief;
use App\Models\PitchAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $activeBriefs = Brief::query()->whereNotIn('status', [BriefStatus::Won, BriefStatus::Lost])->count();
        $won = Brief::query()->where('status', BriefStatus::Won)->count();
        $lost = Brief::query()->where('status', BriefStatus::Lost)->count();
        $pendingPitches = PitchAssignment::query()->where('status', PitchAssignmentStatus::Pending)->count();

        $pipeline = Brief::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [
                is_string($row->status) ? $row->status : $row->status->value => $row->total,
            ]);

        $recentBriefs = Brief::query()
            ->with('creator')
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'active_briefs' => $activeBriefs,
                'won' => $won,
                'lost' => $lost,
                'win_rate' => ($won + $lost) > 0 ? round(($won / ($won + $lost)) * 100, 1) : 0,
                'pending_pitches' => $pendingPitches,
            ],
            'pipeline' => $pipeline,
            'recentBriefs' => $recentBriefs->map(fn (Brief $brief) => [
                'id' => $brief->id,
                'title' => $brief->title,
                'client_name' => $brief->client_name,
                'status' => $brief->status?->value,
                'deadline' => $brief->deadline?->toDateString(),
            ]),
        ]);
    }
}
