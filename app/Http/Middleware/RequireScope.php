<?php

namespace Northstar\Http\Middleware;

use Northstar\Models\Client;
use Closure;

class RequireScope
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
        Client::gate($scope);

        return $next($request);
    }
}
