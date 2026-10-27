<?php

use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('guests get 401', fn () => $this->getJson('/api/admin/applications')->assertUnauthorized());

test('non-admins get 403', function () {
    $this->actingAs(User::factory()->create())->getJson('/api/admin/applications')->assertForbidden();
});

test('admins can list applications with pagination', function () {
    JobApplication::factory(20)->create();

    $this->actingAs($this->admin)->getJson('/api/admin/applications')
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonPath('meta.total', 20);
});

test('admins can create, update and delete an application', function () {
    $job = JobListing::factory()->create();

    $created = $this->actingAs($this->admin)->postJson('/api/admin/applications', [
        'job_listing_id' => $job->id,
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'message' => 'Hello',
        'status' => 'new',
    ])->assertCreated()->json('data.id');

    $this->actingAs($this->admin)->putJson("/api/admin/applications/{$created}", [
        'job_listing_id' => $job->id,
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'message' => 'Hello',
        'status' => 'reviewed',
    ])->assertOk();
    $this->assertDatabaseHas('job_applications', ['id' => $created, 'status' => 'reviewed']);

    $this->actingAs($this->admin)->deleteJson("/api/admin/applications/{$created}")->assertNoContent();
    $this->assertDatabaseMissing('job_applications', ['id' => $created]);
});

test('an application status must be a known value', function () {
    $job = JobListing::factory()->create();

    $this->actingAs($this->admin)->postJson('/api/admin/applications', [
        'job_listing_id' => $job->id,
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'message' => 'Hello',
        'status' => 'maybe',
    ])->assertUnprocessable()->assertJsonValidationErrors(['status']);
});
