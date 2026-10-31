<?php

use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
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

test('applications cannot be created through the admin api', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/admin/applications', [
            'job_listing_id' => JobListing::factory()->create()->id,
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'message' => 'Hello',
            'status' => 'new',
        ])
        ->assertStatus(405);
});

test('admins can update the status of an application', function () {
    $application = JobApplication::factory()->create(['status' => 'new']);

    $this->actingAs($this->admin)
        ->putJson("/api/admin/applications/{$application->id}", [
            'status' => 'reviewed',
        ])
        ->assertOk();

    $this->assertDatabaseHas('job_applications', [
        'id' => $application->id,
        'status' => 'reviewed',
    ]);
});

test('admins can delete an application', function () {
    $application = JobApplication::factory()->create();

    $this->actingAs($this->admin)
        ->deleteJson("/api/admin/applications/{$application->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('job_applications', ['id' => $application->id]);
});

test('an application status must be a known value', function () {
    $application = JobApplication::factory()->create();

    $this->actingAs($this->admin)
        ->putJson("/api/admin/applications/{$application->id}", [
            'status' => 'maybe',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});
