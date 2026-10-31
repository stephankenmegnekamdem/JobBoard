<?php

use App\Enums\AccountType;
use App\Enums\TeamRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admins land on the admin overview after login', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect('/admin');
});

test('the admin overview renders platform stats', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/index')
            ->has('stats.users')
            ->has('stats.companies')
            ->has('stats.openJobs')
            ->has('stats.newApplications'),
        );
});

test('non-admins cannot open the admin overview', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

test('the shared home url matches each account type', function () {
    $admin = User::factory()->admin()->create();
    $seeker = User::factory()->create(['account_type' => AccountType::Seeker]);
    $company = User::factory()->company()->create();

    $this->actingAs($admin)->get('/admin')
        ->assertInertia(fn (Assert $page) => $page->where('homeUrl', '/admin'));

    $this->actingAs($seeker)->get('/applications')
        ->assertInertia(fn (Assert $page) => $page->where('homeUrl', '/applications'));

    $this->actingAs($company)->get('/applications')
        ->assertInertia(fn (Assert $page) => $page->where(
            'homeUrl',
            '/'.$company->currentTeam->slug.'/dashboard',
        ));
});
