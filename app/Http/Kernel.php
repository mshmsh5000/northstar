<?php

namespace Northstar\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Northstar\Http\Middleware\ParseOAuthHeader;

class Kernel extends HttpKernel
{
    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public function bootstrap()
    {
        parent::bootstrap();

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
