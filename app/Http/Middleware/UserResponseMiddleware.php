<?php

namespace Northstar\Http\Middleware;

use Northstar\Models\Campaign;
use Northstar\Models\User;
use Closure;

class UserResponseMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (! is_object($response)) {
            return $response;
        }

        $statusCode = $response->getStatusCode();
        $response = $response->getData();

        // Ensure user objects have all fillable properties set with
        if (isset($response->data)) {
            if (is_array($response->data)) {
                foreach ($response->data as $user) {
                    $this->fillUser($user);
                }
            } elseif (is_object($response->data)) {
                $this->fillUser($response->data);
            }
        }

        return response()->json($response, $statusCode, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * For any fillable User properties that are not hidden, set values to keys
     * that are unset to null.
     *
     * @param $user User object
     */
    private function fillUser(&$user)
    {
        $tmp = new User();

        $fillableNotHidden = array_diff($tmp->getFillable(), $tmp->getHidden());

        foreach ($fillableNotHidden as $key) {
            $user->$key = isset($user->$key) ? $user->$key : null;
        }

        // Fill campaigns activity data too, if any
        if (! empty($user->campaigns) && is_array($user->campaigns)) {
            foreach ($user->campaigns as $campaign) {
                Campaign::populateAllAttributes($campaign);
            }
        }
    }
}
