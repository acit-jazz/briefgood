<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiAnalysisResult;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiUsageDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Admin only.');
        }

        $stats = [
            'total_analyses' => AiAnalysisResult::count(),
            'total_cost_usd' => AiAnalysisResult::sum('cost_usd') ?? 0,
            'total_tokens' => AiAnalysisResult::sum('total_tokens') ?? 0,
            'total_prompt_tokens' => AiAnalysisResult::sum('prompt_tokens') ?? 0,
            'total_completion_tokens' => AiAnalysisResult::sum('completion_tokens') ?? 0,
        ];

        $byModel = AiAnalysisResult::query()
            ->select('model_used')
            ->selectRaw('COUNT(*) as analysis_count')
            ->selectRaw('SUM(prompt_tokens) as total_prompt_tokens')
            ->selectRaw('SUM(completion_tokens) as total_completion_tokens')
            ->selectRaw('SUM(total_tokens) as total_tokens')
            ->selectRaw('SUM(cost_usd) as total_cost_usd')
            ->groupBy('model_used')
            ->orderByDesc('analysis_count')
            ->get();

        $recentAnalyses = AiAnalysisResult::with('brief')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'brief_title' => $r->brief?->title,
                'model_used' => $r->model_used,
                'prompt_tokens' => $r->prompt_tokens,
                'completion_tokens' => $r->completion_tokens,
                'total_tokens' => $r->total_tokens,
                'cost_usd' => $r->cost_usd,
                'created_at' => $r->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/ai-usage-dashboard/Index', [
            'stats' => $stats,
            'byModel' => $byModel,
            'recentAnalyses' => $recentAnalyses,
        ]);
    }
}
