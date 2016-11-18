<?php

namespace Northstar\Http\Middleware;

use Closure;

class SessionVariablesToJavaScript
{
    protected $sessionVariables = ['referrer_uri'];

    /**
     * Run the request filter after the request is handled by the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Request  $request
     */
    public function handle($request, Closure $next)
    {
        foreach($this->sessionVariables as $variable) {
            if (session($variable)) {
                app('JavaScript')->put([
                    camel_case($variable) => session($variable),
                ]);
            }
        }

        return $next($request);
    }

}
