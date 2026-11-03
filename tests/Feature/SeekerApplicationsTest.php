<?php

use App\Models\JobApplication;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('seekers see only their own applications', function () {
    $user = User::factory()->create();
    $mine = JobApplication::factory()->create(['user_id' => $user->id]);
    JobApplication::factory()->create();

    $this->actingAs($user)->get('/applications')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('applications')
            ->has('applications', 1)
            ->where('applications.0.id', $mine->id)
            ->where('applications.0.status', 'new')
            ->where('applications.0.jobTitle', $mine->jobListing->title));
});

test('a seeker with no applications sees an empty list', function () {
    $this->actingAs(User::factory()->create())->get('/applications')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('applications', 0));
});
