<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class BasePolicy
{
    protected string $family = '';

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, Model $model): bool
    {
        return $this->can($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Model $model): bool
    {
        return $this->can($user, 'update');
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->can($user, 'delete');
    }

    protected function can(User $user, string $action): bool
    {
        $permission = $this->family === '' ? $action : "{$this->family}.{$action}";

        return $user->hasPermissionTo($permission);
    }
}
