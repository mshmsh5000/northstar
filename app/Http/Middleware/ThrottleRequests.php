<?php

namespace Northstar\Http\Middleware;

use Closure;
use Northstar\Events\Throttled;
use Illuminate\Routing\Middleware\ThrottleRequests as BaseThrottler;
use Illuminate\Http\JsonResponse;

class ThrottleRequests extends BaseThrottler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  int $maxAttempts
     * @param  int $decayMinutes
     * @return mixed
     */
    public function handle($request, Closure $next, $maxAttempts = 10, $decayMinutes = 15)
    {
        if (! config('features.rate-limiting')) {
            return $next($request);
        }

        return parent::handle($request, $next, $maxAttempts, $decayMinutes);
    }

    /**
     * Create a 'too many attempts' response.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return \Illuminate\Http\Response|JsonResponse
     */
    protected function buildResponse($key, $maxAttempts)
    {
        // Report the rate-limited request to StatHat.
        event(Throttled::class);

        $minutes = ceil($this->limiter->availableIn($key) / 60);
        $pluralizedNoun = $minutes === 1 ? 'minute' : 'minutes';
        $message = 'Too many attempts. Please try again in '.$minutes.' '.$pluralizedNoun.'.';

        if (request()->wantsJson() || request()->ajax()) {
            return new JsonResponse($message, 429);
        }

        return redirect()->back()->with('status', $message);
    }
}
