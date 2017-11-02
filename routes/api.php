<?php

/**
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * contains the "api" middleware group. Now create something great!
 *
 * @var \Illuminate\Routing\Router $router
 * @see \Northstar\Providers\RouteServiceProvider
 */

// https://profile.dosomething.org/v2/
$router->group(['prefix' => 'v2', 'as' => 'v2.'], function () use ($router) {
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

    // Password Reset
    $router->resource('resets', 'ResetController', ['only' => 'store']);

    // Public Key
    $router->get('keys', 'KeyController@index');
    $router->get('key', 'KeyController@show'); // Deprecated.

    // Scopes
    $router->get('scopes', 'ScopeController@index');
});

// https://profile.dosomething.org/v1/
$router->group(['prefix' => 'v1', 'as' => 'v1.'], function () use ($router) {
    // Authentication
    $router->post('auth/token', 'Legacy\AuthController@createToken');
    $router->post('auth/invalidate', 'Legacy\AuthController@invalidateToken');
    $router->post('auth/verify', 'Legacy\AuthController@verify');
    $router->post('auth/register', 'Legacy\AuthController@register');
    $router->post('auth/phoenix', 'Legacy\AuthController@phoenix');

    // Users
    $router->resource('users', 'UserController', ['except' => ['show', 'update']]);
    $router->get('users/{term}/{id}', 'UserController@show');
    $router->put('users/{term}/{id}', 'UserController@update');
    $router->post('users/{id}/avatar', 'AvatarController@store');
    $router->post('users/{id}/merge', 'MergeController@store');

    // Profile (the currently authenticated user)
    $router->get('profile', 'ProfileController@show');
    $router->post('profile', 'ProfileController@update');
    $router->get('profile/signups', 'Legacy\SignupController@profile');
    $router->get('profile/reportbacks', 'Legacy\ReportbackController@profile');

    // Signups & Reportbacks (Phoenix)
    $router->resource('signups', 'Legacy\SignupController', ['only' => ['index', 'show', 'store']]);
    $router->resource('reportbacks', 'Legacy\ReportbackController', ['only' => ['index', 'show', 'store']]);
});

// Discovery
$router->group(['prefix' => '.well-known'], function () use ($router) {
    $router->get('openid-configuration', 'DiscoveryController@index');
});

// Simple health check endpoint
$router->get('/status', function () {
    return ['status' => 'good'];
});
