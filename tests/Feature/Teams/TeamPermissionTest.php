<?php

use App\Enums\TeamRole;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('owners can invite admins', function () {
    Notification::fake();

    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $this->actingAs($owner)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'new-admin@example.com',
            'role' => TeamRole::Admin->value,
        ])
        ->assertRedirect(route('teams.edit', $team));

    $this->assertDatabaseHas('team_invitations', [
        'team_id' => $team->id,
        'email' => 'new-admin@example.com',
        'role' => TeamRole::Admin->value,
    ]);
});

test('admins cannot invite other admins', function () {
    Notification::fake();

    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $admin = User::factory()->create();
    $team->members()->attach($admin, ['role' => TeamRole::Admin->value]);

    $this->actingAs($admin)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'blocked@example.com',
            'role' => TeamRole::Admin->value,
        ])
        ->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('team_invitations', ['email' => 'blocked@example.com']);
});

test('invitations cannot assign the owner role', function () {
    Notification::fake();

    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $this->actingAs($owner)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'second-owner@example.com',
            'role' => TeamRole::Owner->value,
        ])
        ->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('team_invitations', ['email' => 'second-owner@example.com']);
});

test('members cannot send invitations', function () {
    Notification::fake();

    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $member = User::factory()->create();
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($member)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'blocked@example.com',
            'role' => TeamRole::Member->value,
        ])
        ->assertForbidden();
});

test('owners see pending invitations on the team edit page', function () {
    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->get(route('teams.edit', $team))
        ->assertInertia(fn (Assert $page) => $page->has('invitations', 1));
});

test('members cannot see pending invitations on the team edit page', function () {
    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $member = User::factory()->create();
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($member)
        ->get(route('teams.edit', $team))
        ->assertInertia(fn (Assert $page) => $page->has('invitations', 0));
});

test('admins are not offered the admin role unless they own the team', function () {
    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $admin = User::factory()->create();
    $team->members()->attach($admin, ['role' => TeamRole::Admin->value]);

    $this->actingAs($admin)
        ->get(route('teams.edit', $team))
        ->assertInertia(fn (Assert $page) => $page
            ->has('availableRoles', 1)
            ->where('availableRoles.0.value', TeamRole::Member->value),
        );
});

test('owners are offered the admin role', function () {
    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $this->actingAs($owner)
        ->get(route('teams.edit', $team))
        ->assertInertia(fn (Assert $page) => $page->has('availableRoles', 2));
});

test('members expose their profile image as a public url', function () {
    Storage::fake('public');

    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $member = User::factory()->create(['image' => 'profile-images/ada.jpg']);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($owner)
        ->get(route('teams.edit', $team))
        ->assertInertia(fn (Assert $page) => $page
            ->where('members.1.avatar', Storage::disk('public')->url('profile-images/ada.jpg')),
        );
});

test('members without a profile image have a null avatar', function () {
    $owner = User::factory()->company()->create();
    $team = $owner->currentTeam;

    $member = User::factory()->create(['image' => null]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($owner)
        ->get(route('teams.edit', $team))
        ->assertInertia(fn (Assert $page) => $page->where('members.1.avatar', null));
});
