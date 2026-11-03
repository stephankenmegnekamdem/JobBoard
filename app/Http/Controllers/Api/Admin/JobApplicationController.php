<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
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

    /**
     * Admins may create applications directly (the spec requires create on
     * every table). Status is not mass-assignable, so it is set explicitly.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'job_listing_id' => ['required', 'integer', Rule::exists('job_listings', 'id')],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('job_applications', 'email')
                    ->where('job_listing_id', $request->integer('job_listing_id')),
            ],
            'tel_num' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'string', Rule::in(JobApplication::statuses())],
        ]);

        $status = $data['status'];
        unset($data['status']);

        $application = new JobApplication($data);
        $application->forceFill(['status' => $status])->save();

        return response()->json(['data' => $application], 201);
    }

    /**
     * @return array<string, mixed>
     */
    public function show(JobApplication $application): array
    {
        return ['data' => $application->load(['jobListing', 'applicant'])];
    }

    /**
     * Only the status can change. Status is not mass-assignable, so it is
     * set explicitly, and the job, applicant, and message are never touched.
     *
     * @return array<string, mixed>
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
