<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class JobApplicationController extends Controller
{
    public function index(): JsonResource
    {
        return JsonResource::collection(
            JobApplication::with(['jobListing', 'applicant'])->latest()->paginate(15),
        );
    }

    public function show(JobApplication $application): array
    {
        return ['data' => $application->load(['jobListing', 'applicant'])];
    }

    /**
     * Only the status can change. Status is not mass-assignable, so it is
     * set explicitly, and the job, applicant, and message are never touched.
     */
    public function update(Request $request, JobApplication $application): array
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(JobApplication::statuses())],
        ]);

        $application->forceFill(['status' => $data['status']])->save();

        return ['data' => $application];
    }

    public function destroy(JobApplication $application): Response
    {
        $application->delete();

        return response()->noContent();
    }
}
