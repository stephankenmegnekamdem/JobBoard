<?php use App\Models\JobListing;
use App\Models\User;

test('anyone can list published jobs', function () {
    JobListing::factory(15)->create();
    $response = $this->getJson('/api/jobs');
    $response->assertOk()->assertJsonCount(12, 'data')->assertJsonPath('meta.total', 15)->assertJsonStructure(['data' => [['id', 'title', 'short_description', 'company' => ['name']]]]);
});
test('unpublished jobs are hidden', function () {
    JobListing::factory()->create(['published_at' => null]);
    $this->getJson('/api/jobs')->assertOk()->assertJsonCount(0, 'data');
});
test('a job can be viewed in detail', function () {
    $job = JobListing::factory()->create();
    $this->getJson("/api/jobs/{$job->id}")->assertOk()->assertJsonPath('data.title', $job->title)->assertJsonStructure(['data' => ['description', 'company' => ['name', 'location'],             'contact' => ['name']]]);
});
test('an unknown job returns 404', function () {
    $this->getJson('/api/jobs/999')->assertNotFound();
});
test('a guest can apply to a job', function () {
    $job = JobListing::factory()->create();
    $this->postJson("/api/jobs/{$job->id}/applications", ['name' => 'Ada Lovelace',         'email' => 'ada@example.com',         'tel_num' => '555-0100',         'message' => 'I would like to apply.'])->assertCreated();
    $this->assertDatabaseHas('job_applications', ['job_listing_id' => $job->id, 'email' => 'ada@example.com', 'user_id' => null]);
});
test('a logged-in applicant is linked to their application', function () {
    $user = User::factory()->create();
    $job = JobListing::factory()->create();
    $this->actingAs($user)->postJson("/api/jobs/{$job->id}/applications", ['name' => $user->name,         'email' => $user->email,         'message' => 'Hello'])->assertCreated();
    $this->assertDatabaseHas('job_applications', ['job_listing_id' => $job->id, 'user_id' => $user->id]);
});
test('an application needs an email and a message', function () {
    $job = JobListing::factory()->create();
    $this->postJson("/api/jobs/{$job->id}/applications", ['name' => 'X'])->assertUnprocessable()->assertJsonValidationErrors(['email', 'message']);
});
