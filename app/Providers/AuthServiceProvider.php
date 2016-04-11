<?php

namespace Northstar\Providers;

use DateInterval;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\Server as OAuthServer;
use Northstar\Auth\NorthstarTokenGuard;
use Northstar\Auth\Repositories\AccessTokenRepository;
use Northstar\Auth\Repositories\ClientRepository;
use Northstar\Auth\Repositories\RefreshTokenRepository;
use Northstar\Auth\Repositories\ScopeRepository;
use Northstar\Auth\Repositories\UserRepository;
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

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(ScopeRepositoryInterface::class, ScopeRepository::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class);
        $this->app->bind(AccessTokenRepositoryInterface::class, AccessTokenRepository::class);

        // Configure the OAuth authorization server
        $this->app->singleton(OAuthServer::class, function () {
            $server = new OAuthServer(
                app(ClientRepositoryInterface::class),
                app(AccessTokenRepositoryInterface::class),
                app(ScopeRepositoryInterface::class),
                base_path('storage/keys/private.key'),
                base_path('storage/keys/public.key')
            );

            // Enable the password & client credentials grants w/ an access token TTL of 1 hour.
            $passwordGrant = app(PasswordGrant::class);
            $server->enableGrantType($passwordGrant, new DateInterval('PT1H'));

            $clientCredentialsGrant = app(ClientCredentialsGrant::class);
            $server->enableGrantType($clientCredentialsGrant, new DateInterval('PT1H'));

            return $server;
        });

        parent::registerPolicies($gate);
    }
}
