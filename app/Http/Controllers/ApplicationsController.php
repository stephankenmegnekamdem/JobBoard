<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationsController extends Controller
{
    /**
     * The seeker's own applications, newest first, with the job and company.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $applications = JobApplication::query()
            ->where('user_id', $user->id)
            ->with('jobListing.company')
            ->latest()
            ->get()
            ->map(fn (JobApplication $application) => [
                'id' => $application->id,
                'jobTitle' => $application->jobListing->title,
                'company' => $application->jobListing->company?->name,
                'status' => $application->status,
                'appliedAt' => $application->created_at?->toISOString(),
            ])
            ->values()
            ->all();

        return Inertia::render('applications', [
            'applications' => $applications,
            'pendingInvitations' => $user->pendingTeamInvitations(),
        ]);
    }
}
