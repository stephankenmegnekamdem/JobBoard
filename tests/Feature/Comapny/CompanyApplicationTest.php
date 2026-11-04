<?php

use App\Enums\TeamRole;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function applicationForCompany(User $owner): JobApplication
{
    $listing = JobListing::factory()->create([
        'team_id' => $owner->currentTeam->id,
        'contact_id' => $owner->id,
    ]);

    return JobApplication::factory()->create(['job_listing_id' => $listing->id]);
}

test('company members can view an application to their listing', function () {
    $owner = User::factory()->company()->create();
    $application = applicationForCompany($owner);

    $this->actingAs($owner)
        ->get("/{$owner->currentTeam->slug}/applications/{$application->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('company/application')
            ->where('application.id', $application->id));
});

test('other companies cannot view it', function () {
    $owner = User::factory()->company()->create();
    $other = User::factory()->company()->create();
    $application = applicationForCompany($owner);

    $this->actingAs($other)
        ->get("/{$other->currentTeam->slug}/applications/{$application->id}")
        ->assertNotFound();
});

test('company owners can change the status', function () {
    $owner = User::factory()->company()->create();
    $application = applicationForCompany($owner);

    $this->actingAs($owner)
        ->patch(
            "/{$owner->currentTeam->slug}/applications/{$application->id}/status",
            ['status' => 'reviewed']
        )
        ->assertRedirect();

    expect($application->fresh()->status)->toBe('reviewed');
});

test('company members can download the applicant cv', function () {
    Storage::fake('local');

    $owner = User::factory()->company()->create();
    $seeker = User::factory()->create(['cv' => 'cvs/test.pdf']);
    Storage::disk('local')->put('cvs/test.pdf', '%PDF-1.4 test');

    $application = applicationForCompany($owner);
    $application->forceFill(['user_id' => $seeker->id])->save();

    $this->actingAs($owner)
        ->get("/{$owner->currentTeam->slug}/applications/{$application->id}/cv")
        ->assertOk()
        ->assertDownload();
});

test('company admins can change the status', function () {
    $owner = User::factory()->company()->create();
    $admin = User::factory()->create();
    $owner->currentTeam->members()->attach($admin, ['role' => TeamRole::Admin->value]);
    $application = applicationForCompany($owner);

    $this->actingAs($admin)
        ->patch(
            "/{$owner->currentTeam->slug}/applications/{$application->id}/status",
            ['status' => 'reviewed']
        )
        ->assertRedirect();

    expect($application->fresh()->status)->toBe('reviewed');
});

test('plain company members cannot change the status', function () {
    $owner = User::factory()->company()->create();
    $member = User::factory()->create();
    $owner->currentTeam->members()->attach($member, ['role' => TeamRole::Member->value]);
    $application = applicationForCompany($owner);

    $this->actingAs($member)
        ->patch(
            "/{$owner->currentTeam->slug}/applications/{$application->id}/status",
            ['status' => 'reviewed']
        )
        ->assertForbidden();

    expect($application->fresh()->status)->toBe('new');
});

test('plain company members can still view the application, without the status control', function () {
    $owner = User::factory()->company()->create();
    $member = User::factory()->create();
    $owner->currentTeam->members()->attach($member, ['role' => TeamRole::Member->value]);
    $application = applicationForCompany($owner);

    $this->actingAs($member)
        ->get("/{$owner->currentTeam->slug}/applications/{$application->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('company/application')
            ->where('canUpdateStatus', false));

    $this->actingAs($owner)
        ->get("/{$owner->currentTeam->slug}/applications/{$application->id}")
        ->assertInertia(fn (Assert $page) => $page->where('canUpdateStatus', true));
});
