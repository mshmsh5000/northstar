<?php

/**
 * Set routes for the application.
 *
 * @var \Illuminate\Routing\Router $router
 * @see \Northstar\Providers\RouteServiceProvider
 */

// Redirect to some useful documentation on the homepage.
$router->get('/', function () {
    return redirect()->to('https://github.com/DoSomething/api');
});

// Simple health check endpoint
$router->get('/status', function () {
    return ['status' => 'good'];
});

// https://nortstar.dosomething.org/v2/
$router->group(['prefix' => 'v2'], function () use ($router) {
    // Authentication
    if (config('features.oauth')) {
        $router->post('auth/token', 'OAuthController@createToken');
        $router->delete('auth/token', 'OAuthController@invalidateToken');
    }

    // Users
    // ...

    // Profile
    // ...

    // OAuth Clients
    $router->resource('clients', 'ClientController');

    // Scopes
    $router->get('scopes', function () {
        return \Northstar\Auth\Scope::all();
    });
});

// https://northstar.dosomething.org/v1/
$router->group(['prefix' => 'v1'], function () use ($router) {
    // Authentication
    $router->post('auth/token', 'AuthController@createToken');
    $router->post('auth/invalidate', 'AuthController@invalidateToken');
    $router->post('auth/verify', 'AuthController@verify');
    $router->post('auth/register', 'AuthController@register');

    // Users
    $router->resource('users', 'UserController', ['except' => ['show', 'update']]);
    $router->get('users/{term}/{id}', 'UserController@show');
    $router->put('users/{term}/{id}', 'UserController@update');
    $router->post('users/{id}/avatar', 'AvatarController@store');

    // Profile (the currently authenticated user)
    $router->get('profile', 'ProfileController@show');
    $router->post('profile', 'ProfileController@update');
    $router->get('profile/signups', 'SignupController@profile');
    $router->get('profile/reportbacks', 'ReportbackController@profile');

    // Signups & Reportbacks (Phoenix)
    $router->resource('signups', 'SignupController', ['only' => ['index', 'show', 'store']]);
    $router->resource('reportbacks', 'ReportbackController', ['only' => ['index', 'show', 'store']]);

    // API Clients (the artist formerly known as keys)
    $router->resource('keys', 'ClientController');

    $router->get('scopes', function () {
        $scopes = \Northstar\Auth\Scope::all();

        // Format as a single key-value array to keep compatibility.
        return collect($scopes)->map(function ($scope) {
            return $scope['description'];
        });
    });
});
