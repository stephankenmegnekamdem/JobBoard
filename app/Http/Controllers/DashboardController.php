<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The company dashboard. EnsureTeamMembership has already confirmed the
     * user belongs to this team, so only the team's own data is loaded here.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $team = Team::where('slug', $request->route('current_team'))->firstOrFail();

        // Every application to any listing this company owns, open or closed.
        $applications = JobApplication::query()
            ->whereHas('jobListing', fn (Builder $query) => $query->where('team_id', $team->id));

        return Inertia::render('dashboard', [
            'team' => [
                'name' => $team->name,
                'slug' => $team->slug,
            ],
            'stats' => [
                'openJobs' => $team->jobListings()->open()->count(),
                'totalApplications' => (clone $applications)->count(),
                'newApplications' => (clone $applications)
                    ->where('status', JobApplication::STATUS_NEW)
                    ->count(),
                'members' => $team->memberships()->count(),
                'pendingInvites' => $team->invitations()
                    ->whereNull('accepted_at')
                    ->where(fn (Builder $query) => $query
                        ->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', now()))
                    ->count(),
            ],
            'recentApplications' => (clone $applications)
                ->with('jobListing:id,title')
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (JobApplication $application) => [
                    'id' => $application->id,
                    'name' => $application->name,
                    'email' => $application->email,
                    'status' => $application->status,
                    'jobTitle' => $application->jobListing->title,
                    'createdAt' => $application->created_at->toISOString(),
                ])
                ->values()
                ->all(),
            // Invitations addressed to the current user, for the modal.
            'pendingInvitations' => $user->pendingTeamInvitations(),
        ]);
    }
}
