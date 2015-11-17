<?php

namespace Northstar\Http\Middleware;

use Northstar\Models\Token;
use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthenticateToken
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
        $token = $request->header('Session');
        if (! $token) {
            throw new HttpException(401, 'No token found.');
        }

        if (! Token::where('key', '=', $token)->exists()) {
            throw new HttpException(401, 'Token mismatched.');
        }

        return $next($request);
    }
}
