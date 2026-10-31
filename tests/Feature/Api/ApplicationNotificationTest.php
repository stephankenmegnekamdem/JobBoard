<?php

use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use App\Notifications\ApplicationReceived;
use Illuminate\Support\Facades\Notification;

test('the recruiter is notified and the application is marked as emailed', function () {
    Notification::fake();

    $job = JobListing::factory()->open()->create();

    $this->postJson("/api/jobs/{$job->id}/applications", [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'message' => 'Hello',
    ])->assertCreated();

    Notification::assertSentTo($job->contact, ApplicationReceived::class);

    $application = JobApplication::where('email', 'ada@example.com')->firstOrFail();

    expect($application->emailed_at)->not->toBeNull();
});

test('a missing contact does not block the application', function () {
    Notification::fake();

    $job = JobListing::factory()->open()->create();
    $job->update(['contact_id' => null]);

    $this->postJson("/api/jobs/{$job->id}/applications", [
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'message' => 'Hello',
    ])->assertCreated();

    Notification::assertNothingSent();
    $this->assertDatabaseHas('job_applications', ['email' => 'ada@example.com']);
});

test('the notification is queued', function () {
    expect(new ApplicationReceived(1))
        ->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});

test('the public resource does not expose the contact email', function () {
    $job = JobListing::factory()->open()->create();

    $this->getJson("/api/jobs/{$job->id}")
        ->assertOk()
        ->assertJsonPath('data.contact.name', $job->contact->name)
        ->assertJsonMissingPath('data.contact.email');
});

test('a listing without a contact still renders', function () {
    $job = JobListing::factory()->open()->create();
    $job->update(['contact_id' => null]);

    $this->getJson("/api/jobs/{$job->id}")
        ->assertOk()
        ->assertJsonPath('data.contact', null);
});
