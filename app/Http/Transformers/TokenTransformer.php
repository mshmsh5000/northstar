<?php

namespace Northstar\Http\Transformers;

use League\Fractal\TransformerAbstract;
use Northstar\Models\Token;

class TokenTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'user'
    ];

    /**
     * @param Token $token
     * @return array
     */
    public function transform(Token $token)
    {
        return [
            'key' => $token->key,
            'user' => $token->user()
        ];
    }

    /**
     * Include the user which owns the given token.
     *
     * @param Token $token
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(Token $token)
    {
        return $this->item($token->user, new UserTransformer);
    }
}
