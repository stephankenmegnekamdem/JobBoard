<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function show(Request $request, string $current_team, JobApplication $application): Response
    {
        $team = $this->teamFor($current_team, $application);

        $application->load(['jobListing', 'applicant']);

        $applicant = $application->applicant;

        return Inertia::render('company/application', [
            'team' => ['name' => $team->name, 'slug' => $team->slug],
            'application' => [
                'id' => $application->id,
                'name' => $application->name,
                'email' => $application->email,
                'telNum' => $application->tel_num,
                'message' => $application->message,
                'status' => $application->status,
                'appliedAt' => $application->created_at?->toISOString(),
                'jobTitle' => $application->jobListing->title,
            ],
            'applicant' => $applicant ? [
                'name' => trim($applicant->name . ' ' . $applicant->last_name),
                'about' => $applicant->about,
                'address' => $applicant->address,
                'avatarUrl' => $applicant->avatar_url,
                'hasCv' => $applicant->cv !== null,
            ] : null,
            'statuses' => JobApplication::statuses(),
        ]);
    }

    public function cv(Request $request, string $current_team, JobApplication $application): StreamedResponse
    {
        $this->teamFor($current_team, $application);

        $cv = $application->applicant?->cv;

        abort_if($cv === null || ! Storage::disk('local')->exists($cv), 404);

        return Storage::disk('local')->download($cv, $application->name . ' - CV.pdf');
    }

    public function updateStatus(Request $request, string $current_team, JobApplication $application): RedirectResponse
    {
        $this->teamFor($current_team, $application);

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(JobApplication::statuses())],
        ]);

        $application->forceFill(['status' => $data['status']])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Status updated.')]);

        return back();
    }

    /**
     * The team in the URL, after confirming this application was sent to one of its listings.
     */
    private function teamFor(string $teamSlug, JobApplication $application): Team
    {
        $team = Team::where('slug', $teamSlug)->firstOrFail();

        abort_unless($application->jobListing->team_id === $team->id, 404);

        return $team;
    }
}
