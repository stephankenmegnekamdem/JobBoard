<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('shared user data includes a public avatar url', function () {
    Storage::fake('public');

    $user = User::factory()->create(['image' => 'profile-images/ada.jpg']);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.avatar_url', Storage::disk('public')->url('profile-images/ada.jpg')),
        );
});

test('users without a profile image have a null avatar url', function () {
    $user = User::factory()->create(['image' => null]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.avatar_url', null),
        );
});
