<?php

namespace Northstar\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Northstar\Auth\NorthstarTokenGuard;
use Northstar\Models\User;
use Northstar\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        /**
         * The authentication manager.
         * @var \Illuminate\Auth\AuthManager $auth
         */
        $auth = $this->app['auth'];

        // Register our custom token Guard implementation
        $auth->extend('northstar-token', function ($app, $name, array $config) use ($auth) {
            return new NorthstarTokenGuard($auth->createUserProvider($config['provider']), request());
        });

        parent::registerPolicies($gate);
    }
}
