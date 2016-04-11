<?php

namespace Northstar\Auth\Entities;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

class RefreshTokenEntity implements RefreshTokenEntityInterface
{
    use RefreshTokenTrait, EntityTrait;

    /**
     * We do not want our refresh tokens to expire.
     *
     * @return bool
     */
    public function isExpired()
    {
        return false;
    }
}
