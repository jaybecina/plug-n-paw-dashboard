<?php

use App\Models\User;

it('sends guests to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('redirects the home page to the dashboard', function () {
    $this->get('/')->assertRedirect(route('dashboard'));
});

it('lets admins open the dashboard', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertOk();
});

it('forbids verified users who are not admins', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertForbidden();
});

it('has no public sign-up', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', [
        'name' => 'Intruder',
        'email' => 'intruder@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    expect(User::where('email', 'intruder@example.com')->exists())->toBeFalse();
});
