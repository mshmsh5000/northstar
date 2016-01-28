<?php

namespace Northstar\Http\Middleware;

use Auth;
use Northstar\Models\Token;
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
            throw new HttpException(401, 'Authentication token mismatched.');
        }

        return $next($request);
    }
}
