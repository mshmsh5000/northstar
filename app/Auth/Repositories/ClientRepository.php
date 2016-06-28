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
     * @param string      $clientIdentifier   The client's identifier
     * @param string      $grantType          The grant type used
     * @param null|string $clientSecret       The client's secret (if sent)
     * @param bool        $mustValidateSecret If true the client must attempt to validate the secret unless the client
     *                                        is confidential
     *
     * @return \League\OAuth2\Server\Entities\ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        // Fetch client from the database.
        $model = Client::where('client_id', $clientIdentifier)->first();
        if (! $model) {
            return null;
        }

        // If the grant requires us to check the client secret, do that.
        if ($mustValidateSecret && $model->client_secret !== $clientSecret) {
            return null;
        }

        return new ClientEntity($model->client_id, $model->scope);
    }
}
