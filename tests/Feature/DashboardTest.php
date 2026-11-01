<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get('/acme/dashboard')
        ->assertRedirect(route('login'));
});

test('seekers cannot visit a team dashboard they do not belong to', function () {
    $seeker = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach(User::factory()->company()->create(), ['role' => TeamRole::Owner->value]);

    $this->actingAs($seeker)
        ->get(route('dashboard', $team->slug))
        ->assertForbidden();
});

test('company users can visit their team dashboard', function () {
    $user = User::factory()->company()->create();

    $this->actingAs($user)
        ->get(route('dashboard', $user->currentTeam->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('dashboard'));
});

test('dashboard includes pending invitations for the authenticated user', function () {
    $invitee = User::factory()->company()->create(['email' => 'invited@example.com']);

    $owner = User::factory()->create(['name' => 'Taylor Otwell']);
    $team = Team::factory()->create(['name' => 'Laravel Team']);
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($invitee)
        ->get(route('dashboard', $invitee->currentTeam->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->has('pendingInvitations', 1)
            ->where('pendingInvitations.0.code', $invitation->code)
            ->where('pendingInvitations.0.inviterName', 'Taylor Otwell')
            ->where('pendingInvitations.0.team.name', 'Laravel Team')
            ->where('pendingInvitations.0.team.slug', $team->slug)
            ->missing('pendingInvitations.0.teamName'),
        );
});

test('dashboard does not include accepted invitations', function () {
    $invitee = User::factory()->company()->create(['email' => 'invited@example.com']);

    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    TeamInvitation::factory()->accepted()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($invitee)
        ->get(route('dashboard', $invitee->currentTeam->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->has('pendingInvitations', 0),
        );
});

test('dashboard excludes expired invitations without deleting them', function () {
    $invitee = User::factory()->company()->create(['email' => 'invited@example.com']);

    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->expired()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($invitee)
        ->get(route('dashboard', $invitee->currentTeam->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->has('pendingInvitations', 0),
        );

    $this->assertDatabaseHas('team_invitations', ['id' => $invitation->id]);
});

test('dashboard does not include or delete other users invitations', function () {
    $invitee = User::factory()->company()->create(['email' => 'invited@example.com']);

    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'someone@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($invitee)
        ->get(route('dashboard', $invitee->currentTeam->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->has('pendingInvitations', 0),
        );

    $this->assertDatabaseHas('team_invitations', ['id' => $invitation->id]);
});
