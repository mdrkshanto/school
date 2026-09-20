<?php

use App\Enums\Panel;
use App\Models\User;

beforeEach(function (): void {
    seedPermissions();
});

it('renders the portal home page', function (): void {
    $this->get(route('home'))->assertOk();
});

it('renders the login page for a panel', function (): void {
    $this->get(route('panel.admin.login'))->assertOk();
    $this->get(route('panel.teacher.login'))->assertOk();
});

it('logs in a teacher and redirects to the teacher dashboard', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('teacher');

    $response = $this->post(route('panel.teacher.login.attempt'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('teacher.dashboard'));
    $this->assertAuthenticated('teacher');
});

it('rejects a teacher with the wrong panel guard', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('teacher');

    $this->post(route('panel.admin.login.attempt'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('admin');
});

it('rejects an inactive user even with the right role', function (): void {
    $user = User::factory()->create(['is_active' => false]);
    $user->assignRole('teacher');

    $this->post(route('panel.teacher.login.attempt'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('teacher');
});

it('does not leak one panel session into another panel', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('teacher');

    $this->post(route('panel.teacher.login.attempt'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->get(route('student.dashboard'))->assertRedirect(route('panel.student.login'));
});

it('redirects unauthenticated guests to the panel login', function (): void {
    $this->get(route('admin.dashboard'))->assertRedirect(route('panel.admin.login'));

    foreach (Panel::cases() as $panel) {
        $this->get(route("{$panel->value}.dashboard"))
            ->assertRedirect(route("panel.{$panel->value}.login"));
    }
});

it('logs the user out and returns to the panel login', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('teacher');

    $this->actingAs($user, 'teacher')
        ->post(route('panel.teacher.logout'))
        ->assertRedirect(route('panel.teacher.login'));

    $this->assertGuest('teacher');
});
