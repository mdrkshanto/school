<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Create every role and permission the middleware checks.
 */
function seedPermissions(): void
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $families = [
        'dashboard', 'students', 'teachers', 'parents', 'classes', 'subjects',
        'sessions', 'attendance', 'exams', 'results', 'grades', 'fees',
        'invoices', 'payments', 'notices', 'events', 'library', 'transport',
        'users', 'roles', 'settings', 'reports',
    ];

    $permissions = ['dashboard.view', 'attendance.view', 'attendance.manage', 'users.manage', 'roles.manage', 'settings.manage', 'reports.view', 'payments.create', 'payments.view'];

    foreach ($families as $family) {
        if (in_array($family, ['dashboard', 'attendance', 'users', 'roles', 'settings', 'reports', 'payments'], true)) {
            continue;
        }
        foreach (['view', 'create', 'update', 'delete'] as $action) {
            $permissions[] = "{$family}.{$action}";
        }
    }

    collect($permissions)->unique()->each(fn (string $name) => Permission::firstOrCreate(['name' => $name]));

    foreach (['admin', 'teacher', 'student', 'parent'] as $role) {
        Role::firstOrCreate(['name' => $role]);
    }
}

/**
 * Create a user with the given roles and grant them all or the given permissions.
 *
 * @param  string|string[]  $roles
 * @param  string[]|null  $permissions
 */
function panelUser(string|array $roles, ?array $permissions = null): User
{
    seedPermissions();

    $user = User::factory()->create(['is_active' => true]);

    foreach ((array) $roles as $role) {
        $user->assignRole($role);
    }

    if ($permissions === null) {
        $user->givePermissionTo(...Permission::query()->pluck('name'));
    } else {
        $user->givePermissionTo(...array_values($permissions));
    }

    return $user;
}
