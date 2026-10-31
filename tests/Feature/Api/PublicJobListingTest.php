<?php

use App\Models\JobListing;
use App\Models\Team;

test('the public index only lists open listings', function () {
    $open = JobListing::factory()->create([
        'published_at' => now()->subDay(),
        'expires_at' => now()->addWeek(),
    ]);
    JobListing::factory()->create(['published_at' => null]);
    JobListing::factory()->create([
        'published_at' => now()->subMonth(),
        'expires_at' => now()->subDay(),
    ]);
    JobListing::factory()->create(['published_at' => now()->addDay()]);

    $this->getJson('/api/jobs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $open->id);
});

test('closed listings return 404 when viewed directly', function () {
    $expired = JobListing::factory()->create([
        'published_at' => now()->subMonth(),
        'expires_at' => now()->subDay(),
    ]);

    $this->getJson("/api/jobs/{$expired->id}")->assertNotFound();
});

test('open listings can be viewed', function () {
    $job = JobListing::factory()->create([
        'published_at' => now()->subDay(),
        'expires_at' => now()->addWeek(),
    ]);

    $this->getJson("/api/jobs/{$job->id}")->assertOk();
});

test('search matches titles and keywords', function () {
    JobListing::factory()->create([
        'title' => 'Math Teacher',
        'published_at' => now()->subDay(),
    ]);
    JobListing::factory()->create([
        'title' => 'Nurse',
        'keywords' => 'healthcare, math',
        'published_at' => now()->subDay(),
    ]);
    JobListing::factory()->create([
        'title' => 'Driver',
        'published_at' => now()->subDay(),
    ]);

    $this->getJson('/api/jobs?q=math')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a percent sign in search does not match every listing', function () {
    JobListing::factory()->create(['title' => 'Math Teacher', 'published_at' => now()->subDay()]);
    JobListing::factory()->create(['title' => 'Nurse', 'published_at' => now()->subDay()]);

    $this->getJson('/api/jobs?q=%25')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('an underscore in search does not act as a wildcard', function () {
    JobListing::factory()->create(['title' => 'Math Teacher', 'published_at' => now()->subDay()]);

    $this->getJson('/api/jobs?q=M_th')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('the public detail does not expose the contact account', function () {
    $job = JobListing::factory()->create([
        'published_at' => now()->subDay(),
        'expires_at' => now()->addWeek(),
    ]);

    $this->getJson("/api/jobs/{$job->id}")
        ->assertOk()
        ->assertJsonMissingPath('data.contact.email');
});
