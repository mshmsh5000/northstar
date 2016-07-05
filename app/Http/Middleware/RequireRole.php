<?php

namespace Northstar\Http\Middleware;

use League\OAuth2\Server\Exception\OAuthServerException;
use Northstar\Auth\Scope;
use Closure;

class RequireRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param $role
     * @return mixed
     * @throws OAuthServerException
     */
    public function handle($request, Closure $next, $role)
    {
        // The 'admin' scope should grant admin privileges, even if the
        // authorized user doesn't have that role.
        if ($role === 'admin' && Scope::allows('admin')) {
            return $next($request);
        }

        // First, does this client allow us to do things with this role?
        Scope::gate('role:'.$role);

        if (auth()->user()->role !== $role) {
            throw OAuthServerException::accessDenied('The authenticated user must have the `'.$role.'` role.');
        }

        return $next($request);
    }
}
