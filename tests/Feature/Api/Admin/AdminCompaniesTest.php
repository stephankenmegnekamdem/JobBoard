<?php

use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('guests get 401', fn () => $this->getJson('/api/admin/companies')->assertUnauthorized());

test('non-admins get 403', function () {
    $this->actingAs(User::factory()->create())->getJson('/api/admin/companies')->assertForbidden();
});

test('admins can list companies with pagination', function () {
    Team::factory(20)->create();

    $this->actingAs($this->admin)->getJson('/api/admin/companies')
        ->assertOk()
        ->assertJsonCount(15, 'data');
});

test('admins can create, update and delete a company', function () {
    $created = $this->actingAs($this->admin)->postJson('/api/admin/companies', [
        'name' => 'Acme',
        'location' => 'Houston, TX',
    ])->assertCreated()->json('data.id');

    $this->actingAs($this->admin)->putJson("/api/admin/companies/{$created}", [
        'name' => 'Acme Ltd',
    ])->assertOk();
    $this->assertDatabaseHas('teams', ['id' => $created, 'name' => 'Acme Ltd']);

    $this->actingAs($this->admin)->deleteJson("/api/admin/companies/{$created}")->assertNoContent();
    $this->assertSoftDeleted('teams', ['id' => $created]);
});
