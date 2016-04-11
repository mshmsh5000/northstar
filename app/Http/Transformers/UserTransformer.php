<?php

namespace Northstar\Http\Transformers;

use Northstar\Models\Client;
use Northstar\Models\User;
use League\Fractal\TransformerAbstract;
use Gate;

class UserTransformer extends TransformerAbstract
{
    /**
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        $response = [
            'id' => $user->_id,
            '_id' => $user->_id, // @DEPRECATED: Will be removed.
            'email' => $user->email,
            'mobile' => $user->mobile,

            'first_name' => $user->first_name,
        ];

        if (Client::allows('admin') || Gate::allows('view-full-profile', $user)) {
            $response['last_name'] = $user->last_name;
        }

        $response['last_initial'] = $user->last_initial;

        $response['photo'] = $user->photo;
        $response['interests'] = $user->interests;

        if (Client::allows('admin') || Gate::allows('view-full-profile', $user)) {
            $response['birthdate'] = $user->birthdate;

            $response['addr_street1'] = $user->addr_street1;
            $response['addr_street2'] = $user->addr_street2;
            $response['addr_city'] = $user->addr_city;
            $response['addr_state'] = $user->addr_state;
            $response['addr_zip'] = $user->addr_zip;
        }

        $response['language'] = $user->language;
        $response['country'] = $user->country;

        // Signup source (e.g. drupal, cgg, mobile...)
        $response['source'] = $user->source;

        // Internal & third-party service IDs:
        $response['drupal_id'] = $user->drupal_id;
        $response['mobilecommons_id'] = $user->mobilecommons_id;
        $response['parse_installation_ids'] = $user->parse_installation_ids;

        $response['mobilecommons_status'] = $user->mobilecommons_status;

        $response['updated_at'] = $user->updated_at->toISO8601String();
        $response['created_at'] = $user->created_at->toISO8601String();

        return $response;
    }
}
