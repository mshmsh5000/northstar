<?php

namespace Northstar\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;
use League\OAuth2\Server\AuthorizationServer;
use Northstar\Listeners\ReportFailedAuthenticationAttempt;
use Northstar\Listeners\ReportSuccessfulAuthentication;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [ReportSuccessfulAuthentication::class],
        Failed::class => [ReportFailedAuthenticationAttempt::class],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        // Rate limit failed client authentication attempts.
        // @see: OAuthController::createToken
        $oauth = app(AuthorizationServer::class);
        $oauth->getEmitter()->addListener('client.authentication.failed', function () {
            // Increment number of failed requests for this route & IP address.
            app(RateLimiter::class)->hit(request()->fingerprint(), 1);
        });

        // Update count on StatHat every time a route is hit.
        // e.g. will increment the "northstar - v1/users/{term}/{id}" stat each
        // time a client attempts to view a user profile through that route.
        $events->listen(RouteMatched::class, function (RouteMatched $match) {
            app('stathat')->ezCount('route: '.$match->route->getUri());
        });
    }
}
