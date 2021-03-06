<?php

namespace Northstar\Auth\Repositories;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Northstar\Auth\Entities\AuthCodeEntity;
use Northstar\Models\AuthCode;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * Creates a new AuthCode
     *
     * @return AuthCodeEntityInterface
     */
    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }

    /**
     * Persists a new auth code to permanent storage.
     *
     * @param AuthCodeEntityInterface $authCodeEntity
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        AuthCode::create([
            'code' => $authCodeEntity->getIdentifier(),
            'scopes' => $authCodeEntity->getScopes(),
            'expiration' => $authCodeEntity->getExpiryDateTime(),
            'user_id' => $authCodeEntity->getUserIdentifier(),
            'client_id' => $authCodeEntity->getClient()->getIdentifier(),
            'redirect_uri' => $authCodeEntity->getRedirectUri(),
        ]);
    }

    /**
     * Revoke an auth code.
     *
     * @param string $codeId
     */
    public function revokeAuthCode($codeId)
    {
        AuthCode::where('code', $codeId)->delete();
    }

    /**
     * Check if the auth code has been revoked.
     *
     * @param string $codeId
     * @return bool Return true if this code has been revoked
     */
    public function isAuthCodeRevoked($codeId)
    {
        return ! AuthCode::where('code', $codeId)->exists();
    }
}
