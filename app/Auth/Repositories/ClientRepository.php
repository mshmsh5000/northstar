<?php

namespace Northstar\Auth\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Northstar\Auth\Entities\ClientEntity;
use Northstar\Models\Client;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     * @param string $grantType The grant type used
     * @param null|string $clientSecret The client's secret (if sent)
     *
     * @return \League\OAuth2\Server\Entities\ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null)
    {
        // Fetch client from the database & make OAuth2 entity
        $model = Client::where('app_id', $clientIdentifier)->first();
        
        if(! $model) {
            return null;
        }
        
        return new ClientEntity($model);
    }
}
