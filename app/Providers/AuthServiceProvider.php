<?php

namespace Northstar\Providers;

use DateInterval;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Server as OAuthServer;
use Northstar\Auth\NorthstarTokenGuard;
use Northstar\Auth\Storage\AccessTokenRepository;
use Northstar\Auth\Storage\ClientRepository;
use Northstar\Auth\Storage\RefreshTokenRepository;
use Northstar\Auth\Storage\ScopeRepository;
use Northstar\Auth\Storage\UserRepository;
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
        
        // Configure the OAuth authorization server
        $this->app->singleton(OAuthServer::class, function() {
            $userRepository = new UserRepository();
            $refreshTokenRepository = new RefreshTokenRepository();

            $server = new OAuthServer(
                new ClientRepository(),
                new AccessTokenRepository(),
                new ScopeRepository(),
                app_path('storage/keys/private.key'),
                app_path('storage/keys/public.key')
            );

            // Enable the password grant on the server with an access token TTL of 1 hour
            $server->enableGrantType(
                new PasswordGrant($userRepository, $refreshTokenRepository),
                new DateInterval('PT1H')
            );

            // Enable the client credentials grant on the server with a token TTL of 1 hour
            $server->enableGrantType(
                new ClientCredentialsGrant(),
                new DateInterval('PT1H')
            );
            
            return $server;
        });

        parent::registerPolicies($gate);
    }
}
