<?php

namespace Northstar\Auth\Entities;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use Northstar\Models\Client;

class ClientEntity implements ClientEntityInterface
{
    use EntityTrait, ClientTrait;

    protected $secret;

    protected $allowedScopes;

    /**
     * Make a new OAuth Client entity.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->name = $client->client_id;
        $this->secret = $client->client_secret;
        $this->allowedScopes = $client->scope;

        // @TODO: Save this per client!
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
}
