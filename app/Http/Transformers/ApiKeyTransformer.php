<?php

namespace Northstar\Http\Transformers;

use Northstar\Models\ApiKey;
use League\Fractal\TransformerAbstract;

class ApiKeyTransformer extends TransformerAbstract
{
    /**
     * @param ApiKey $key
     * @return array
     */
    public function transform(ApiKey $key)
    {
        return [
            'app_id' => $key->app_id,
            'api_key' => $key-> api_key,
            'scope' => $key->scope,

            'updated_at' => $key->updated_at->toISO8601String(),
            'created_at' => $key->created_at->toISO8601String(),
        ];
    }
}
