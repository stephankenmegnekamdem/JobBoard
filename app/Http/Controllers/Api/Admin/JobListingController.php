<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class JobListingController extends Controller
{
    public function index(): JsonResource
    {
        return JsonResource::collection(
            JobListing::with(['company', 'contact'])->latest()->paginate(15),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $job = JobListing::create([
            ...$data,
            'published_at' => now(),
        ]);

        return response()->json(['data' => $job], 201);
    }

    public function show(JobListing $job): array
    {
        return ['data' => $job->load(['company', 'contact'])];
    }

    public function update(Request $request, JobListing $job): array
    {
        $job->update($this->validated($request));

        return ['data' => $job];
    }

    public function destroy(JobListing $job): Response
    {
        $job->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'team_id' => ['required', 'integer', Rule::exists('teams', 'id')->whereNull('deleted_at')],
            'contact_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'string', 'max:255'],
            'working_time' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'url', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $isMember = Team::query()
            ->whereKey($data['team_id'])
            ->whereHas('members', fn ($query) => $query->where('users.id', $data['contact_id']))
            ->exists();

        abort_unless(
            $isMember,
            422,
            'The contact must be a member of the company.',
        );

        return $data;
    }
}
