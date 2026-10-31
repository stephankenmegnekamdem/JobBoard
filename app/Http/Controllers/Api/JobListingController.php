<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobListingDetailResource;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Database\Eloquent\Builder;

class JobListingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $this->searchTerm($request);

        $jobs = JobListing::query()
            ->open()
            ->with('company')
            ->when($search !== null, fn (Builder $query) => $query->where(
                fn (Builder $where) => $where
                    ->where('title', 'like', $search)
                    ->orWhere('keywords', 'like', $search),
            ))
            ->latest('published_at')
            ->paginate(12);

        return JobListingResource::collection($jobs);
    }

    public function show(JobListing $jobListing): JobListingDetailResource|JsonResponse
    {
        if (! $jobListing->isOpen()) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return new JobListingDetailResource($jobListing->load('company'));
    }

    /**
     * Build a LIKE pattern from the search term, escaping wildcard characters
     * so user input cannot match everything.
     */
    private function searchTerm(Request $request): ?string
    {
        $term = trim((string) $request->query('q', ''));

        if ($term === '') {
            return null;
        }

        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term);

        return "%{$escaped}%";
    }
}
