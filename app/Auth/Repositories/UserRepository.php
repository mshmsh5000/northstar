<?php

namespace Northstar\Auth\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Northstar\Auth\Entities\UserEntity;
use Northstar\Auth\Registrar;

class UserRepository implements UserRepositoryInterface
{
    protected $registrar;

    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;
    }

    /**
     * Get a user entity.
     *
     * @param string $username
     * @param string $password
     * @param string $grantType The grant type used
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity
     *
     * @return \League\OAuth2\Server\Entities\UserEntityInterface
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity)
    {
        $credentials = ['username' => $username, 'password' => $password];
        $user = $this->registrar->resolve($credentials);

        if (! $user || ! $this->registrar->verify($user, $credentials)) {
            return null;
        }

        $entity = new UserEntity();
        $entity->setIdentifier($user->id);

        return $entity;
    }
}
