<?php

namespace Northstar\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,

        // @TODO: Enable this once we've updated PHP version.
        //  \Northstar\Http\Middleware\ParseOAuthHeader::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Northstar\Http\Middleware\Authenticate::class,
        'guest' => \Northstar\Http\Middleware\RedirectIfAuthenticated::class,
        'scope' => \Northstar\Http\Middleware\RequireScope::class,

        // @TODO: Remove this old alias for the scope middleware.
        'key' => \Northstar\Http\Middleware\RequireScope::class,
    ];
}
