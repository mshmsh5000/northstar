<?php

namespace Northstar\Providers;

use DateInterval;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResourceServer;
use Northstar\Auth\NorthstarTokenGuard;
use Northstar\Auth\NorthstarUserProvider;
use Northstar\Auth\Registrar;
use Northstar\Auth\Repositories\AccessTokenRepository;
use Northstar\Auth\Repositories\AuthCodeRepository;
use Northstar\Auth\Repositories\ClientRepository;
use Northstar\Auth\Repositories\RefreshTokenRepository;
use Northstar\Auth\Repositories\ScopeRepository;
use Northstar\Auth\Repositories\UserRepository;
use Northstar\Auth\Responses\BearerTokenResponse;

class OAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * The authentication manager.
         * @var \Illuminate\Auth\AuthManager $auth
         */
        $auth = $this->app['auth'];

        // Register our custom user provider
        $auth->provider('northstar', function ($app, array $config) {
            return new NorthstarUserProvider($app[Registrar::class], $app['hash'], $config['model']);
        });

        // Register our custom token Guard implementation
        $auth->extend('northstar-token', function ($app, $name, array $config) use ($auth) {
            return new NorthstarTokenGuard($auth->createUserProvider($config['provider']), request());
        });

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(ScopeRepositoryInterface::class, ScopeRepository::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class);
        $this->app->bind(AccessTokenRepositoryInterface::class, AccessTokenRepository::class);
        $this->app->bind(AuthCodeRepositoryInterface::class, AuthCodeRepository::class);

        // Auth Code grant needs auth code TTL to be injected.
        $this->app->bind(AuthCodeGrant::class, function () {
            return new AuthCodeGrant(
                app(AuthCodeRepositoryInterface::class),
                app(RefreshTokenRepositoryInterface::class),
                new DateInterval('PT1H')
            );
        });

        // Configure the OAuth authorization server
        $this->app->singleton(AuthorizationServer::class, function () {
            $server = new AuthorizationServer(
                app(ClientRepositoryInterface::class),
                app(AccessTokenRepositoryInterface::class),
                app(ScopeRepositoryInterface::class),
                $this->makeCryptKey('private.key'),
                config('app.key'),
                app(BearerTokenResponse::class)
            );

            // Define which OAuth grants we'll accept.
            $grants = [
                AuthCodeGrant::class,
                RefreshTokenGrant::class,
                ClientCredentialsGrant::class,
            ];

            if (config('features.password-grant')) {
                $grants[] = PasswordGrant::class;
            }

            // Enable each grant w/ an access token TTL of 1 hour.
            foreach ($grants as $grant) {
                $server->enableGrantType(app($grant), new DateInterval('PT1H'));
            }

            // Rate limit failed client authentication attempts.
            // @see: OAuthController::createToken
            $server->getEmitter()->addListener('client.authentication.failed', function () {
                // Increment number of failed requests for this route & IP address.
                app(RateLimiter::class)->hit(request()->fingerprint(), 1);
            });

            return $server;
        });

        $this->app->singleton(ResourceServer::class, function () {
            return new ResourceServer(
                app(AccessTokenRepositoryInterface::class),
                $this->makeCryptKey('public.key')
            );
        });
    }

    /**
     * Create a CryptKey instance.
     *
     * @param string $key
     * @return \League\OAuth2\Server\CryptKey
     */
    protected function makeCryptKey($key)
    {
        $shouldCheckPermissions = config('app.debug') === false;
        $path = 'file://'.storage_path('keys/'.$key);

        return new CryptKey($path, null, $shouldCheckPermissions);
    }
}
