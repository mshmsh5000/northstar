<?php

namespace Northstar\Http\Middleware;

use Northstar\Models\ApiKey;
use Closure;

class AuthenticateAPIKey
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
        ApiKey::gate($scope);

        return $next($request);
    }
}
