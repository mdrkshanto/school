<?php

namespace App\Policies;

class UserPolicy extends BasePolicy
{
    protected string $family = 'users';

    public function manage(User $user): bool
    {
        return $this->can($user, 'manage');
    }
}
