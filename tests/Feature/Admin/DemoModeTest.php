<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['demo.enabled' => true]);
    $this->admin = User::factory()->admin()->create();
});

it('refuses password changes in demo mode', function () {
    $this->actingAs($this->admin)
        ->from(route('security.edit'))
        ->withSession(['auth.password_confirmed_at' => time()])
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect(route('security.edit'))
        ->assertInertiaFlash('toast.type', 'error');

    expect(Hash::check('password', $this->admin->fresh()->password))->toBeTrue();
});

it('refuses profile edits in demo mode', function () {
    $this->actingAs($this->admin)
        ->patch(route('profile.update'), ['name' => 'Hacked', 'email' => 'hacked@example.com'])
        ->assertRedirect();

    expect($this->admin->fresh()->name)->not->toBe('Hacked');
});

it('refuses account deletion in demo mode', function () {
    $this->actingAs($this->admin)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect();

    expect($this->admin->fresh())->not->toBeNull();
});

it('allows the same changes when demo mode is off', function () {
    config(['demo.enabled' => false]);

    $this->actingAs($this->admin)
        ->patch(route('profile.update'), ['name' => 'Renamed', 'email' => $this->admin->email])
        ->assertRedirect();

    expect($this->admin->fresh()->name)->toBe('Renamed');
});

it('shows the demo credentials on the login page', function () {
    $this->get(route('login'))->assertInertia(fn (Assert $page) => $page
        ->component('auth/Login')
        ->where('demo.email', config('demo.admin.email'))
        ->where('demo.password', config('demo.admin.password'))
    );
});

it('hides the credentials when demo mode is off', function () {
    config(['demo.enabled' => false]);

    $this->get(route('login'))->assertInertia(fn (Assert $page) => $page
        ->component('auth/Login')
        ->where('demo', null)
    );
});
