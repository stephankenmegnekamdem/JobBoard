<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\JobListing;

class JobApplicationController extends Controller
{
    public function store(StoreJobApplicationRequest $request, JobListing $jobListing)
    {
        $application = $jobListing->applications()->create([...$request->validated(),        'user_id' => $request->user('sanctum')?->id]);

        return response()->json(['data' => ['id' => $application->id]], 201);
    }
}
