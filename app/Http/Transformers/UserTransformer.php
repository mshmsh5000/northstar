<?php

namespace Northstar\Http\Transformers;

use Northstar\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    /**
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id' => $user->_id,
            '_id' => $user->_id, // @DEPRECATED: Will be removed.

            'first_name' => $user->first_name,
            'last_initial' => $user->last_initial,
            'photo' => $user->photo,
            'interests' => $user->interests,
            'country' => $user->country,

            // @TODO: Restrict these, based on API key scope or authenticated user.
            'email' => $user->email,
            'mobile' => $user->mobile,
            // 'last_name' => $user->last_name,
            // 'birthdate' => $user->birthdate,
            // 'race' => $user->race,
            // 'religion' => $user->religion,
            // 'addr_street1' => $user->addr_street1,
            // 'addr_street2' => $user->addr_street2,
            // 'addr_city' => $user->addr_city,
            // 'addr_state' => $user->addr_state,
            // 'addr_zip' => $user->addr_zip,

            // References to app-specific user IDs.
            'drupal_id' => $user->drupal_id,
            'cgg_id' => $user->cgg_id,
            'agg_id' => $user->agg_id,
            'source' => $user->source,

            'parse_installation_ids' => $user->parse_installation_ids,

            'updated_at' => $user->updated_at->toISO8601String(),
            'created_at' => $user->created_at->toISO8601String(),
        ];
    }
}
