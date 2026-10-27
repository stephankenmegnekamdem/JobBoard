<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationController extends Controller
{
    public function index()
    {
        return JsonResource::collection(JobApplication::with('jobListing')->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $application = JobApplication::create($this->validated($request));

        return response()->json(['data' => $application], 201);
    }

    public function show(JobApplication $application)
    {
        return ['data' => $application->load(['jobListing', 'applicant'])];
    }

    public function update(Request $request, JobApplication $application)
    {
        $application->update($this->validated($request));

        return ['data' => $application];
    }

    public function destroy(JobApplication $application)
    {
        $application->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'job_listing_id' => ['required', 'exists:job_listings,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'tel_num' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'in:new,reviewed,rejected,hired'], ]);
    }
}
