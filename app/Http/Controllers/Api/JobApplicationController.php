<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\JobListing;
use App\Notifications\ApplicationReceived;

class JobApplicationController extends Controller
{
    public function store(StoreJobApplicationRequest $request, JobListing $jobListing)
    {
        $application = $jobListing->applications()->create([...$request->validated(),        'user_id' => $request->user('sanctum')?->id]);
        $jobListing->contact->notify(new ApplicationReceived($application));
        $application->update(['emailed_at' => now()]);

        return response()->json(['data' => ['id' => $application->id]], 201);
    }
}
