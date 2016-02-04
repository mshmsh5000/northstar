<?php

namespace Northstar\Http\Middleware;

use Auth;
use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws HttpException
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            throw new HttpException(401, 'You cannot do this with an active authentication token.');
        }

        return $next($request);
    }
}
