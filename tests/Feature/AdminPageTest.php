<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login from the admin page', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

test('non-admins cannot open the admin page', function () {
    $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
});

test('admins can open the admin page', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/index'));
});
