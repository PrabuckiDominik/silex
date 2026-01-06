<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Enums\Role;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Administrator->value);
    }

    public function view(User $user, User $targetUser): bool
    {
        return $user->hasRole('Role::Administrator->value') && $targetUser->hasRole(Role::User->value);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Administrator->value);
    }

    public function update(User $user, User $targetUser): bool
    {
        return $user->hasRole(Role::Administrator->value) && $targetUser->hasRole(Role::User->value);
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $user->hasRole(Role::Administrator->value) && $targetUser->hasRole(Role::User->value);
    }

        public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
