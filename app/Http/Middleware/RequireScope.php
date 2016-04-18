<?php

namespace Northstar\Http\Middleware;

use Northstar\Auth\Scope;
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
        Scope::gate($scope);

        return $next($request);
    }
}
