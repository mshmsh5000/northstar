<?php

namespace Northstar\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Northstar\Http\Middleware\ParseOAuthHeader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;

class Kernel extends HttpKernel
{
    /**
     * Create a new HTTP kernel instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Routing\Router  $router
     */
    public function __construct(Application $app, Router $router)
    {
        parent::__construct($app, $router);

        // Conditionally apply OAuth middleware if feature flag is set.
        if (config('features.oauth')) {
            $this->middleware[] = ParseOAuthHeader::class;
        }
    }

    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
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
    ];
}
