<?php

use App\Models\JobListing;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});
test('guests get 401', fn () => $this->getJson('/api/admin/jobs')->assertUnauthorized());
test('non-admins get 403', function () {
    $this->actingAs(User::factory()->create())->getJson('/api/admin/jobs')->assertForbidden();
});
test('admins can list jobs with pagination', function () {
    JobListing::factory(20)->create();
    $this->actingAs($this->admin)->getJson('/api/admin/jobs')->assertOk()->assertJsonCount(15, 'data')->assertJsonPath('meta.total', 20);
});
test('admins can create, update and delete a job', function () {
    $team = Team::factory()->create();
    $created = $this->actingAs($this->admin)->postJson('/api/admin/jobs', ['team_id' => $team->id,        'contact_id' => $this->admin->id,        'title' => 'Math Teacher',        'short_description' => 'Teach math.',        'description' => 'Full description.'])->assertCreated()->json('data.id');
    $this->actingAs($this->admin)->putJson("/api/admin/jobs/{$created}", ['team_id' => $team->id,        'contact_id' => $this->admin->id,        'title' => 'Science Teacher',        'short_description' => 'Teach science.',        'description' => 'Full description.'])->assertOk();
    $this->assertDatabaseHas('job_listings', ['id' => $created, 'title' => 'Science Teacher']);
    $this->actingAs($this->admin)->deleteJson("/api/admin/jobs/{$created}")->assertNoContent();
    $this->assertDatabaseMissing('job_listings', ['id' => $created]);
});
