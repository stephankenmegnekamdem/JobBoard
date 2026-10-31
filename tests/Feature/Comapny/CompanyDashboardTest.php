<?php

use App\Enums\TeamRole;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('company members can open the company dashboard', function () {
    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $this->actingAs($owner)
        ->get(route('dashboard', $team->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('team.slug', $team->slug)
            ->has('stats')
            ->has('recentApplications'),
        );
});

test('users outside the company cannot open its dashboard', function () {
    $owner = User::factory()->company()->create();
    $outsider = User::factory()->company()->create();

    $this->actingAs($outsider)
        ->get(route('dashboard', $owner->currentTeam->slug))
        ->assertForbidden();
});

test('stats count only this company\'s open listings and applications', function () {
    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $open = JobListing::factory()->open()->create(['team_id' => $team->id]);
    JobListing::factory()->expired()->create(['team_id' => $team->id]);
    JobListing::factory()->unpublished()->create(['team_id' => $team->id]);

    JobApplication::factory()->count(2)->create(['job_listing_id' => $open->id]);
    JobApplication::factory()->create([
        'job_listing_id' => $open->id,
        'status' => JobApplication::STATUS_REVIEWED,
    ]);

    $otherListing = JobListing::factory()->open()->create();
    JobApplication::factory()->count(4)->create(['job_listing_id' => $otherListing->id]);

    $this->actingAs($owner)
        ->get(route('dashboard', $team->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.openJobs', 1)
            ->where('stats.totalApplications', 3)
            ->where('stats.newApplications', 2)
            ->where('stats.members', $team->memberships()->count()),
        );
});

test('recent applications only include this company\'s applications', function () {
    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $mine = JobListing::factory()->open()->create(['team_id' => $team->id]);
    $theirs = JobListing::factory()->open()->create();

    JobApplication::factory()->create(['job_listing_id' => $mine->id, 'email' => 'mine@example.com']);
    JobApplication::factory()->create(['job_listing_id' => $theirs->id, 'email' => 'theirs@example.com']);

    $this->actingAs($owner)
        ->get(route('dashboard', $team->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->has('recentApplications', 1)
            ->where('recentApplications.0.email', 'mine@example.com')
            ->where('recentApplications.0.jobTitle', $mine->title),
        );
});

test('recent applications are limited to eight, newest first', function () {
    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $listing = JobListing::factory()->open()->create(['team_id' => $team->id]);
    JobApplication::factory()->count(10)->create(['job_listing_id' => $listing->id]);

    $this->actingAs($owner)
        ->get(route('dashboard', $team->slug))
        ->assertInertia(fn (Assert $page) => $page->has('recentApplications', 8));
});

test('the company dashboard lists the team members', function () {
    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $member = User::factory()->create();
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($member)
        ->get(route('dashboard', $team->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.members', 2),
        );
});
