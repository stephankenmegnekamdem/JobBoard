<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;

test('company owners cannot delete their account while they own a company', function () {
    $owner = User::factory()->company()->create();

    $this->actingAs($owner)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertSessionHasErrors('password');

    expect($owner->fresh())->not->toBeNull();
});

test('members of a company can delete their account', function () {
    $owner = User::factory()->company()->create();
    $member = User::factory()->create();

    $owner->currentTeam->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($member)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect('/');

    expect(User::find($member->id))->toBeNull();
});

test('seekers can delete their account', function () {
    $seeker = User::factory()->create();

    $this->actingAs($seeker)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect('/');

    expect(User::find($seeker->id))->toBeNull();
});
