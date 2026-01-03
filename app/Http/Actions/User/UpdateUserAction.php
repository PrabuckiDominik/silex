<?php

namespace App\Http\Actions\User;

use App\Models\User;

class UpdateUserAction
{
    public function __invoke(User $user, array $userData): User
    {
        $user->update($userData);
        activity()
            ->performedOn($user)
            ->log('User updated');
        return $user;
    }
}
