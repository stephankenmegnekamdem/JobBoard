<?php
use Laravel\Sanctum\Sanctum;
use App\Enums\AccountType;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
});

test('guests get 401', fn () => $this->getJson('/api/admin/users')->assertUnauthorized());

test('non-admins get 403', function () {
    $this->actingAs(User::factory()->create())->getJson('/api/admin/users')->assertForbidden();
});

test('admins can list users with pagination', function () {
    User::factory(20)->create();

    $this->actingAs($this->admin)->getJson('/api/admin/users')
        ->assertOk()
        ->assertJsonCount(15, 'data');
});

test('admins can create, update and delete a user', function () {
    $created = $this->actingAs($this->admin)->postJson('/api/admin/users', [
        'name' => 'Grace',
        'last_name' => 'Hopper',
        'email' => 'grace@example.com',
        'password' => 'password123',
    ])->assertCreated()->json('data.id');

    $user = User::find($created);

    expect($user->account_type)->toBe(AccountType::Seeker);
    expect($user->teams()->count())->toBe(0);

    $this->actingAs($this->admin)->putJson("/api/admin/users/{$created}", [
        'name' => 'Grace M.',
        'last_name' => 'Hopper',
        'email' => 'grace@example.com',
    ])->assertOk();
    $this->assertDatabaseHas('users', ['id' => $created, 'name' => 'Grace M.']);

    $this->actingAs($this->admin)->deleteJson("/api/admin/users/{$created}")->assertNoContent();
    $this->assertDatabaseMissing('users', ['id' => $created]);
});

test('a new user needs a password', function () {
    $this->actingAs($this->admin)->postJson('/api/admin/users', [
        'name' => 'Grace',
        'last_name' => 'Hopper',
        'email' => 'grace@example.com',
    ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
});
