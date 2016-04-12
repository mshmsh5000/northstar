<?php

namespace Northstar\Auth\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Northstar\Auth\Entities\ScopeEntity;
use Northstar\Models\Client;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     *
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        $scopes = Client::scopes();

        if (array_key_exists($identifier, $scopes) === false) {
            return null;
        }

        $entity = new ScopeEntity();
        $entity->setIdentifier($identifier);

        return $entity;
    }

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string $grantType
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity
     * @param null|string $userIdentifier
     *
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface[]
     */
    public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null)
    {
        // Get a plain array of the requested scopes to compare.
        $scopes = array_map(function (ScopeEntity $scope) {
            return $scope->getIdentifier();
        }, $scopes);

        // Intersect with the list of allowed scopes for that client.
        $allowedScopes = $clientEntity->getAllowedScopes();
        $filteredScopes = array_intersect($scopes, $allowedScopes);

        // Return an array of filtered ClientEntities
        return array_map(function ($scope) {
            $entity = new ScopeEntity();
            $entity->setIdentifier($scope);

            return $entity;
        }, $filteredScopes);
    }
}
