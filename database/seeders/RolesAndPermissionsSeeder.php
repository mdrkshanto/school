<?php

namespace Database\Seeders;

use App\Enums\PermissionFamily;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * @return string[]
     */
    private function allPermissions(): array
    {
        $perms = [
            'dashboard.view',
            'attendance.view', 'attendance.manage',
            'users.manage', 'roles.manage', 'settings.manage', 'reports.view',
            'payments.create', 'payments.view',
        ];

        foreach (PermissionFamily::cases() as $family) {
            if (in_array($family->value, ['dashboard', 'attendance', 'users', 'roles', 'settings', 'reports', 'payments'], true)) {
                continue;
            }
            $perms = array_merge($perms, $family->fullCrud());
        }

        return array_values(array_unique($perms));
    }

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = $this->allPermissions();
        $byName = collect($permissions)
            ->mapWithKeys(fn (string $name) => [$name => Permission::firstOrCreate(['name' => $name])]);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $student = Role::firstOrCreate(['name' => 'student']);
        $parent = Role::firstOrCreate(['name' => 'parent']);

        $admin->syncPermissions($permissions);

        $teacher->syncPermissions([
            'dashboard.view',
            'students.view',
            'classes.view',
            'subjects.view',
            'sessions.view',
            'attendance.view',
            'attendance.manage',
            'exams.view',
            'results.view',
            'results.create',
            'results.update',
            'grades.view',
            'notices.view',
            'events.view',
            'library.view',
            'payments.view',
            'reports.view',
        ]);

        $student->syncPermissions([]);
        $parent->syncPermissions([]);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@school.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'phone' => '01000000000',
                'is_active' => true,
            ]
        );
        $adminUser->assignRole($admin);
    }
}