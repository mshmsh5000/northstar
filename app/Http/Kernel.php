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
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        'Illuminate\Cookie\Middleware\EncryptCookies',
        'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        'Illuminate\Session\Middleware\StartSession',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => 'Northstar\Http\Middleware\Authenticate',
        'auth.token' => 'Northstar\Http\Middleware\AuthenticateToken',
        'auth.api' => 'Northstar\Http\Middleware\AuthenticateAPI',
        'guest' => 'Northstar\Http\Middleware\RedirectIfAuthenticated',
        'user' => 'Northstar\Http\Middleware\UserResponseMiddleware',
        'campaign' => 'Northstar\Http\Middleware\CampaignResponseMiddleware',
    ];
}
