<?php

/**
 * Set routes for the application.
 *
 * @var \Illuminate\Routing\Router $router
 * @see \Northstar\Providers\RouteServiceProvider
 */

// Web Experience for https://northstar.dosomething.org/

$router->group(['namespace' => 'Web', 'guard' => 'web', 'middleware' => ['web']], function () use ($router) {
    $router->get('/', 'UserController@home');

    // Authorization flow for the Auth Code OAuth grant.
    $router->get('authorize', 'OAuthController@authorize');
    $router->resource('users', 'UsersController', ['except' => ['index', 'create', 'delete']]);

    // Authorization flow for the Auth Code OAuth grant.
    $router->get('authorize', 'AuthController@authorize');

    // Login & Logout
    $router->get('login', 'AuthController@getLogin');
    $router->post('login', 'AuthController@postLogin');
    $router->get('logout', 'AuthController@getLogout');

    // Registration
    $router->get('register', 'AuthController@getRegister');
    $router->post('register', 'AuthController@postRegister');

    // Password Reset
    if (config('features.password-reset')) {
        $this->get('password/reset/{token?}', 'PasswordController@showResetForm');
        $this->post('password/email', 'PasswordController@sendResetLinkEmail');
        $this->post('password/reset', 'PasswordController@reset');
    } else {
        // If feature flag is disabled, just redirect to Phoenix's reset form.
        $this->get('password/reset/{token?}', function () {
            return redirect(config('services.drupal.url').'/user/password');
        });
    }
});

// API experience for https://nortstar.dosomething.org/v2/
$router->group(['prefix' => 'v2', 'middleware' => ['api']], function () use ($router) {
    // Authentication
    $router->post('auth/token', 'OAuthController@createToken');
    $router->get('auth/info', 'OAuthController@info');
    $router->delete('auth/token', 'OAuthController@invalidateToken');

    // Users
    // ...

    // Profile
    // ...

    // OAuth Clients
    $router->resource('clients', 'ClientController');

    // Public Key
    $router->get('key', 'KeyController@show');

    // Scopes
    $router->get('scopes', 'ScopeController@index');
});

// API experience for https://northstar.dosomething.org/v1/
$router->group(['prefix' => 'v1', 'middleware' => ['api']], function () use ($router) {
    // Authentication
    $router->post('auth/token', 'Legacy\AuthController@createToken');
    $router->post('auth/invalidate', 'Legacy\AuthController@invalidateToken');
    $router->post('auth/verify', 'Legacy\AuthController@verify');
    $router->post('auth/register', 'Legacy\AuthController@register');
    $router->post('auth/phoenix', 'Legacy\AuthController@phoenix');
    $router->post('auth/facebook/validate', 'FacebookController@validateToken');

    // Users
    $router->resource('users', 'UserController', ['except' => ['show', 'update']]);
    $router->get('users/{term}/{id}', 'UserController@show');
    $router->put('users/{term}/{id}', 'UserController@update');
    $router->post('users/{id}/avatar', 'AvatarController@store');

    // Profile (the currently authenticated user)
    $router->get('profile', 'ProfileController@show');
    $router->post('profile', 'ProfileController@update');
    $router->get('profile/signups', 'Legacy\SignupController@profile');
    $router->get('profile/reportbacks', 'Legacy\ReportbackController@profile');

    // Signups & Reportbacks (Phoenix)
    $router->resource('signups', 'Legacy\SignupController', ['only' => ['index', 'show', 'store']]);
    $router->resource('reportbacks', 'Legacy\ReportbackController', ['only' => ['index', 'show', 'store']]);
});

// Simple health check endpoint
$router->get('/status', function () {
    return ['status' => 'good'];
});
