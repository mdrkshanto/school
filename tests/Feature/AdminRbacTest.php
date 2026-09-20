<?php

use function Pest\Laravel\actingAs;

it('forbids an admin without the students.view permission from viewing students', function (): void {
    $admin = panelUser('admin', ['dashboard.view']);

    actingAs($admin, 'admin')
        ->get(route('admin.students.index'))
        ->assertForbidden();
});

it('forbids an admin without the users.manage permission from managing users', function (): void {
    $admin = panelUser('admin', ['dashboard.view']);

    actingAs($admin, 'admin')
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

it('forbids an admin without the results.create permission from generating results', function (): void {
    $admin = panelUser('admin', ['dashboard.view']);

    actingAs($admin, 'admin')
        ->post(route('admin.results.generate'), [
            'exam_id' => 1,
            'class_id' => 1,
        ])
        ->assertForbidden();
});

it('allows an admin with the classes.view permission to view classes', function (): void {
    $admin = panelUser('admin', ['dashboard.view', 'classes.view']);

    actingAs($admin, 'admin')
        ->get(route('admin.classes.index'))
        ->assertOk();
});

it('allows an admin with every permission to reach the dashboard', function (): void {
    $admin = panelUser('admin');

    actingAs($admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk();
});
