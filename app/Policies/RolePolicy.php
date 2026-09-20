<?php

namespace App\Policies;

class RolePolicy extends BasePolicy
{
    protected string $family = 'roles';

    public function manage(User $user): bool
    {
        return $this->can($user, 'manage');
    }
}
