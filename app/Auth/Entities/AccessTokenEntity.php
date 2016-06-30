<?php

namespace Northstar\Auth\Entities;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessTokenEntity implements AccessTokenEntityInterface
{
    use EntityTrait, AccessTokenTrait, TokenEntityTrait;

    /**
     * Generate a JWT from the access token. We override this method from
     * AccessTokenTrait so that we can add the `iss` and `role` claims.
     *
     * @param \League\OAuth2\Server\CryptKey $privateKey
     * @return string
     */
    public function convertToJWT(CryptKey $privateKey)
    {
        return (new Builder())
            ->setIssuer(url('/'))
            ->setAudience($this->getClient()->getIdentifier())
            ->setId($this->getIdentifier(), true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration($this->getExpiryDateTime()->getTimestamp())
            ->setSubject($this->getUserIdentifier())
            ->set('role', 'user') // @TODO: Get from user.
            ->set('scopes', $this->getScopes())
            ->sign(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()))
            ->getToken();
    }
}
