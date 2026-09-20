<?php

namespace App\Policies;

class SettingPolicy extends BasePolicy
{
    protected string $family = 'settings';

    public function manage(User $user): bool
    {
        return $this->can($user, 'manage');
    }
}
