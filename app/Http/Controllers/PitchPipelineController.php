<?php

namespace App\Http\Controllers;

use App\Enums\PitchAssignmentStatus;
use App\Http\Resources\BriefResource;
use App\Mail\PitchAssignmentCreated;
use App\Models\PitchAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public function accept(PitchAssignment $pitchAssignment): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('acceptAssignment', $pitchAssignment);

        $pitchAssignment->update([
            'status' => PitchAssignmentStatus::Accepted,
        ]);

        return back()->with('success', 'Assignment accepted.');
    }

    public function decline(Request $request, PitchAssignment $pitchAssignment): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('declineAssignment', $pitchAssignment);

        $pitchAssignment->update([
            'status' => PitchAssignmentStatus::Rejected,
            'rejection_reason' => $request->input('reason'),
        ]);

        return back()->with('success', 'Assignment declined.');
    }

    public function sendNotification(PitchAssignment $pitchAssignment): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('sendNotification', $pitchAssignment);

        $brief = $pitchAssignment->brief;
        if (!$pitchAssignment->pic || !$pitchAssignment->pic->email) {
            return back()->with('error', 'No PIC email found for this assignment.');
        }

        try {
            Mail::to($pitchAssignment->pic->email)->send(new PitchAssignmentCreated($pitchAssignment, $brief));

            $pitchAssignment->update([
                'notified_at' => now(),
            ]);

            Log::info('PitchAssignment email sent manually', [
                'assignment_id' => $pitchAssignment->id,
                'pic_email' => $pitchAssignment->pic->email,
                'brief_id' => $brief->id,
            ]);

            return back()->with('success', 'Email sent to ' . $pitchAssignment->pic->email);
        } catch (\Throwable $e) {
            Log::error('Failed to send PitchAssignment email', [
                'assignment_id' => $pitchAssignment->id,
                'pic_email' => $pitchAssignment->pic->email,
                'brief_id' => $brief->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
