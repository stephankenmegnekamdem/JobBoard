<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobListingDetailResource;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobListingController extends Controller
{
    /**
     * List published job listings, newest first, 12 per page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = $request->query('q');

        $jobs = JobListing::query()
            ->with('company')
            ->whereNotNull('published_at')
            ->when($q, fn ($query) => $query->where(fn ($where) => $where
                ->where('title', 'like', "%{$q}%")
                ->orWhere('keywords', 'like', "%{$q}%")))
            ->latest('published_at')
            ->paginate(12);

        return JobListingResource::collection($jobs);
    }

    /**
     * Show every detail of one listing ("Learn more").
     */
    public function show(JobListing $jobListing): JobListingDetailResource
    {
        return new JobListingDetailResource($jobListing->load(['company', 'contact']));
    }
}
