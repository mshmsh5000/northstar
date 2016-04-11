<?php

namespace Northstar\Auth\Entities;

use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use Northstar\Models\User;

class UserEntity implements UserEntityInterface
{
    use EntityTrait;

    public function __construct(User $user)
    {
        $this->setIdentifier($user->id);
    }
}
