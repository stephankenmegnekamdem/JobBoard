<?php

use App\Enums\TeamRole;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;

function applicationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'message' => 'I would like to apply.',
    ], $overrides);
}

test('applications can be submitted to an open listing', function () {
    $job = JobListing::factory()->create([
        'published_at' => now()->subDay(),
        'expires_at' => now()->addWeek(),
    ]);

    $this->postJson("/api/jobs/{$job->id}/applications", applicationPayload())
        ->assertSuccessful();

    $this->assertDatabaseHas('job_applications', [
        'job_listing_id' => $job->id,
        'email' => 'ada@example.com',
        'status' => 'new',
    ]);
});

test('applications cannot be submitted to an unpublished listing', function () {
    $job = JobListing::factory()->create(['published_at' => null]);

    $this->postJson("/api/jobs/{$job->id}/applications", applicationPayload())
        ->assertNotFound();

    $this->assertDatabaseCount('job_applications', 0);
});

test('applications cannot be submitted to an expired listing', function () {
    $job = JobListing::factory()->create([
        'published_at' => now()->subMonth(),
        'expires_at' => now()->subDay(),
    ]);

    $this->postJson("/api/jobs/{$job->id}/applications", applicationPayload())
        ->assertNotFound();

    $this->assertDatabaseCount('job_applications', 0);
});

test('the same email cannot apply twice to one listing, regardless of case', function () {
    $job = JobListing::factory()->create(['published_at' => now()->subDay()]);

    $this->postJson("/api/jobs/{$job->id}/applications", applicationPayload())
        ->assertSuccessful();

    $this->postJson("/api/jobs/{$job->id}/applications", applicationPayload([
        'email' => 'ADA@Example.com',
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    expect(JobApplication::where('job_listing_id', $job->id)->count())->toBe(1);
});

test('the same email can apply to different listings', function () {
    $first = JobListing::factory()->create(['published_at' => now()->subDay()]);
    $second = JobListing::factory()->create(['published_at' => now()->subDay()]);

    $this->postJson("/api/jobs/{$first->id}/applications", applicationPayload())->assertSuccessful();
    $this->postJson("/api/jobs/{$second->id}/applications", applicationPayload())->assertSuccessful();

    expect(JobApplication::where('email', 'ada@example.com')->count())->toBe(2);
});

test('a logged in user cannot apply twice under a different email', function () {
    $job = JobListing::factory()->create(['published_at' => now()->subDay()]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson("/api/jobs/{$job->id}/applications", applicationPayload())
        ->assertSuccessful();

    $this->actingAs($user)
        ->postJson("/api/jobs/{$job->id}/applications", applicationPayload([
            'email' => 'other@example.com',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('the database rejects a duplicate application even if validation is bypassed', function () {
    $job = JobListing::factory()->create();

    JobApplication::factory()->create([
        'job_listing_id' => $job->id,
        'email' => 'ada@example.com',
    ]);

    expect(fn () => JobApplication::factory()->create([
        'job_listing_id' => $job->id,
        'email' => 'ada@example.com',
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

test('deleting a recruiter keeps their listings without a contact', function () {
    $team = \App\Models\Team::factory()->create();
    $recruiter = User::factory()->create();
    $team->members()->attach($recruiter, ['role' => TeamRole::Owner->value]);

    $job = JobListing::factory()->create([
        'team_id' => $team->id,
        'contact_id' => $recruiter->id,
    ]);

    $recruiter->forceDelete();

    expect($job->fresh())->not->toBeNull()
        ->and($job->fresh()->contact_id)->toBeNull();
});

test('applications are saved when the recruiter account has been deleted', function () {
    $job = JobListing::factory()->create(['published_at' => now()->subDay()]);
    $job->update(['contact_id' => null]);

    $this->postJson("/api/jobs/{$job->id}/applications", applicationPayload())
        ->assertCreated();

    $this->assertDatabaseHas('job_applications', [
        'job_listing_id' => $job->id,
        'email' => 'ada@example.com',
    ]);
});

test('applicants cannot choose their own status', function () {
    $job = JobListing::factory()->create(['published_at' => now()->subDay()]);

    $this->postJson("/api/jobs/{$job->id}/applications", applicationPayload([
        'status' => 'hired',
    ]))->assertCreated();

    $this->assertDatabaseHas('job_applications', [
        'job_listing_id' => $job->id,
        'status' => 'new',
    ]);
});
