<?php

use App\Enums\AccountType;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function registrationData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test',
        'last_name' => 'Example',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'account_type' => AccountType::Seeker->value,
    ], $overrides);
}

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('registration screen includes team invitation context', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Laravel Team']);
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this->get(route('register', ['invitation' => $invitation->code]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/register')
        ->where('teamInvitation.code', $invitation->code)
        ->where('teamInvitation.teamName', 'Laravel Team'),
    );
});

test('seekers can register without a team', function () {
    $response = $this->post(route('register.store'), registrationData());

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user->account_type)->toBe(AccountType::Seeker);
    expect($user->teams()->count())->toBe(0);

    $response->assertRedirect('/applications');
});

test('companies register with a company team they own', function () {
    $response = $this->post(route('register.store'), registrationData([
        'account_type' => AccountType::Company->value,
        'company_name' => 'Acme Ltd',
    ]));

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user->account_type)->toBe(AccountType::Company);

    $this->assertDatabaseHas('teams', [
        'name' => 'Acme Ltd',
        'slug' => 'acme-ltd',
        'is_personal' => false,
    ]);

    $this->assertDatabaseHas('team_members', [
        'user_id' => $user->id,
        'role' => TeamRole::Owner->value,
    ]);

    $response->assertRedirect('/acme-ltd/dashboard');
});

test('company registration requires a company name', function () {
    $response = $this->post(route('register.store'), registrationData([
        'account_type' => AccountType::Company->value,
    ]));

    $response->assertSessionHasErrors('company_name');
    $this->assertGuest();
});

test('registration requires a valid account type', function () {
    $response = $this->post(route('register.store'), registrationData([
        'account_type' => 'admin',
    ]));

    $response->assertSessionHasErrors('account_type');
    $this->assertGuest();
});

test('invited signups do not create a company team', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'test@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->post(route('register.store'), registrationData([
        'account_type' => AccountType::Seeker->value,
        'invitation' => $invitation->code,
    ]));

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user->teams()->count())->toBe(0);
    expect($invitation->fresh()->accepted_at)->toBeNull();
});
