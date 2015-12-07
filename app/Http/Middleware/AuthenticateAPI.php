<?php

namespace Northstar\Http\Middleware;

use Northstar\Models\ApiKey;
use Closure;

class AuthenticateAPI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string $scope
     * @return mixed
     */
    public function handle($request, Closure $next, $scope = 'user')
    {
        $app_id = $request->header('X-DS-Application-Id');
        $api_key = $request->header('X-DS-REST-API-Key');

        $key = ApiKey::where('app_id', $app_id)->where('api_key', $api_key)->first();

        if (! $key) {
            return response()->json('Unauthorized access.', 401);
        }

        if(! in_array($scope, $key->scope)) {
            return response()->json('API key is missing required scope.', 403);
        }

        return $next($request);
    }
}
