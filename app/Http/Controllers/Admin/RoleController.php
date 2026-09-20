<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PermissionFamily;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class RoleController extends Controller
{
    public function index()
    {
        $roles = SpatieRole::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (SpatieRole $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'guards' => $role->guard_name,
                'permissions' => $role->permissions->pluck('name'),
                'users_count' => $role->users()->count(),
            ]);

        $permissionGroups = collect(PermissionFamily::cases())
            ->map(fn (PermissionFamily $family) => [
                'label' => ucfirst(str_replace('_', ' ', $family->value)),
                'permissions' => Permission::query()
                    ->where('name', 'LIKE', "{$family->value}.%")
                    ->orderBy('name')
                    ->pluck('name'),
            ])
            ->filter(fn ($group) => $group['permissions']->isNotEmpty())
            ->values();

        return Inertia::render('admin/Roles/Index', [
            'roles' => $roles,
            'permissionGroups' => $permissionGroups,
        ]);
    }

    public function updatePermissions(Request $request, SpatieRole $role)
    {
        $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($request->input('permissions', []));

        $this->successFlash("Permissions updated for {$role->name}.");

        return back();
    }
}
