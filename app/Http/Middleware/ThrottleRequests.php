<?php

namespace Northstar\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Middleware\ThrottleRequests as LaravelThrottleMiddleware;
use Northstar\Auth\Scope;

class ThrottleRequests extends LaravelThrottleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return mixed
     */
    public function handle($request, Closure $next, $maxAttempts = null, $decayMinutes = 60)
    {
        // If the current request has the 'unlimited' scope, do not rate limit.
        if (Scope::allows('unlimited')) {
            return $next($request);
        }

        // Allow 5000 requests/hour per authorized user, or 50 per IP for anonymous requests.
        if (is_null($maxAttempts)) {
            $isAuthenticatedRequest = ! is_null($request->get('oauth_access_token_id'));
            $maxAttempts = $isAuthenticatedRequest ? 5000 : 50;
        }

        return parent::handle($request, $next, $maxAttempts, $decayMinutes);
    }

    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        return $request->get('oauth_user_id', sha1($request->ip()));
    }

    /**
     * Create a 'too many attempts' response.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return \Illuminate\Http\Response
     */
    protected function buildResponse($key, $maxAttempts)
    {
        $response = new JsonResponse(['error' => [
            'code' => 429,
            'message' => 'Too many attempts.',
        ]], 429);

        $retryAfter = $this->limiter->availableIn($key);
        $remainingAttempts = $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter);

        return $this->addHeaders($response, $maxAttempts, $remainingAttempts, $retryAfter);
    }
}
