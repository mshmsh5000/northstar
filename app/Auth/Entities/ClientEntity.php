<?php

namespace Northstar\Auth\Entities;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientEntity implements ClientEntityInterface
{
    use EntityTrait, ClientTrait;

    /**
     * The scopes that this client is allowed to claim.
     * @var array
     */
    protected $allowedScopes;

    /**
     * Make a new OAuth Client entity.
     *
     * @param $client_id
     * @param $scopes
     */
    public function __construct($client_id, $scopes)
    {
        $this->name = $client_id; // @TODO: If we store a human-readable client name, use here.
        $this->allowedScopes = $scopes;
        $this->identifier = $client_id;

        // @TODO: Will need this for authentication code flow. Save this per client!
        $this->redirectUri = '';
    }

    /**
     * Get the scopes that are allowed for this client.
     *
     * @return array
     */
    public function getAllowedScopes()
    {
        return $this->allowedScopes;
    }

    /**
     * Set the allowed scopes for this client.
     *
     * @param $scopes
     */
    public function setAllowedScopes($scopes)
    {
        $this->allowedScopes = $scopes;
    }
}
