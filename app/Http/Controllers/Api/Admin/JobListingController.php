<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListingController extends Controller
{
    public function index()
    {
        return JsonResource::collection(JobListing::latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $job = JobListing::create([...$this->validated($request), 'published_at' => now()]);

        return response()->json(['data' => $job], 201);
    }

    public function show(JobListing $job)
    {
        return ['data' => $job->load(['company', 'contact'])];
    }

    public function update(Request $request, JobListing $job)
    {
        $job->update($this->validated($request));

        return ['data' => $job];
    }

    public function destroy(JobListing $job)
    {
        $job->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'contact_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'string', 'max:255'],
            'working_time' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'url', 'max:255'],
            'image' => ['nullable', 'string', 'max:255']]);
    }
}
