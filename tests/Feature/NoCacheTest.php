<?php

use App\Models\User;

test('pages for logged-in users are not cached by the browser', function () {
    $response = $this->actingAs(User::factory()->create())->get('/settings/profile');

    $response->assertOk()->assertHeader('Pragma', 'no-cache');
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

test('the public job board stays cacheable', function () {
    expect($this->get('/')->headers->get('Cache-Control'))->not->toContain('no-store');
});
