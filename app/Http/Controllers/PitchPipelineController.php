<?php

namespace App\Http\Controllers;

use App\Http\Resources\BriefResource;
use App\Models\PitchAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PitchPipelineController extends Controller
{
    public function index(Request $request): Response
    {
        $assignments = PitchAssignment::query()
            ->with(['brief', 'businessUnit'])
            ->latest()
            ->get()
            ->groupBy(fn (PitchAssignment $assignment) => $assignment->status?->value ?? 'pending');

        return Inertia::render('pitch-pipeline/Index', [
            'columns' => $assignments->map(fn ($items, $status) => [
                'status' => $status,
                'items' => $items->map(fn (PitchAssignment $assignment) => [
                    'id' => $assignment->id,
                    'confidence' => $assignment->confidence,
                    'brief' => BriefResource::make($assignment->brief)->resolve(),
                    'business_unit' => $assignment->businessUnit?->name,
                ]),
            ])->values(),
        ]);
    }
}
