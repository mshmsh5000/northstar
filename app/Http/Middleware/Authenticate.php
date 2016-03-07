<?php

namespace Northstar\Http\Middleware;

use Auth;
use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Authenticate
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
        if (! Auth::check()) {
            app('stathat')->ezCount('invalid auth token error');
            throw new HttpException(401, 'Authentication token mismatched.');
        }

        return $next($request);
    }
}
