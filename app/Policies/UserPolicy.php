<?php

namespace Northstar\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Northstar\Models\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the authorized user can see full profile details
     * for the given user account.
     *
     * @param User $user
     * @param User $profile
     * @return bool
     */
    public function viewFullProfile(User $user, User $profile)
    {
        if (in_array($user->role, ['admin', 'staff'])) {
            return true;
        }

        return $user->id === $profile->id;
    }

    /**
     * Determine if the authorized user can edit the profile details
     * for the given user account.
     *
     * @param User $user
     * @param User $profile
     * @return bool
     */
    public function editProfile(User $user, User $profile)
    {
        if (in_array($user->role, ['admin', 'staff'])) {
            return true;
        }

        return $user->id === $profile->id;
    }
}
