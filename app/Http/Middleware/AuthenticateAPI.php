<?php namespace Northstar\Http\Middleware;

use Northstar\Models\ApiKey;
use Closure;

class AuthenticateAPI
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $app_id = $request->header('X-DS-Application-Id');
        $api_key = $request->header('X-DS-REST-API-Key');

        if (!ApiKey::where("app_id", '=', $app_id)->where("api_key", '=', $api_key)->exists()) {
            return response()->json("Unauthorized access.", 404);
        }

        return $next($request);
    }

}
