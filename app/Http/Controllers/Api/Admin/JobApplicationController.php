<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class JobApplicationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return JsonResource::collection(JobApplication::with('jobListing')->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $application = JobApplication::create($this->validated($request));

        return response()->json(['data' => $application], 201);
    }

    public function show(JobApplication $application): JsonResponse
    {
        return response()->json(['data' => $application->load(['jobListing', 'applicant'])]);
    }

    public function update(Request $request, JobApplication $application): JsonResponse
    {
        $application->update($this->validated($request));

        return response()->json(['data' => $application]);
    }

    public function destroy(JobApplication $application): Response
    {
        $application->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'job_listing_id' => ['required', 'exists:job_listings,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'tel_num' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'in:new,reviewed,rejected,hired'],
        ]);
    }
}
