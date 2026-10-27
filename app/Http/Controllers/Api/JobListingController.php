<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobListingDetailResource;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobListingController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $jobs = JobListing::query()->with('company')->whereNotNull('published_at')->when($q, fn ($query) => $query->where(fn ($where) => $where->where('title', 'like', "%{$q}%")->orWhere('keywords', 'like', "%{$q}%")))->latest('published_at')->paginate(12);

        return JobListingResource::collection($jobs);
    }

    public function show(JobListing $jobListing)
    {
        return new JobListingDetailResource($jobListing->load(['company', 'contact']));
    }
}
