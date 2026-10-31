<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Notifications\ApplicationReceived;
use Illuminate\Http\JsonResponse;

class JobApplicationController extends Controller
{
    public function store(StoreJobApplicationRequest $request, JobListing $jobListing): JsonResponse
    {
        $application = new JobApplication($request->validated());

        $application->forceFill([
            'job_listing_id' => $jobListing->id,
            'user_id' => $request->user('sanctum')?->id,
            'status' => JobApplication::STATUS_NEW,
        ])->save();

        $this->notifyContact($application, $jobListing);

        return response()->json(['data' => ['id' => $application->id]], 201);
    }

    private function notifyContact(JobApplication $application, JobListing $jobListing): void
    {
        $contact = $jobListing->contact;

        if ($contact === null) {
            return;
        }

        $contact->notify(new ApplicationReceived($application->id));

        $application->forceFill(['emailed_at' => now()])->save();
    }
}
