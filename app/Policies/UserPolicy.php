<?php

namespace Northstar\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Northstar\Models\ApiKey;
use Northstar\Models\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * If current request is made with an admin-scoped API key,
     * grant "superuser" privileges.
     * @return bool
     */
    public function before()
    {
        $token = ApiKey::current();
        if ($token && $token->hasScope('admin')) {
            return true;
        }
    }

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
        return $user->id === $profile->id;
    }
}
