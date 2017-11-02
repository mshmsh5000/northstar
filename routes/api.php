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

    // Scopes
    $router->get('scopes', 'ScopeController@index');
});

// https://profile.dosomething.org/v1/
$router->group(['prefix' => 'v1', 'as' => 'v1.'], function () use ($router) {
    // Authentication
    $router->post('auth/verify', 'Legacy\AuthController@verify');

    // Users
    $router->resource('users', 'UserController', ['except' => ['show', 'update']]);
    $router->get('users/{term}/{id}', 'UserController@show');
    $router->put('users/{term}/{id}', 'UserController@update');
    $router->post('users/{id}/avatar', 'AvatarController@store');
    $router->post('users/{id}/merge', 'MergeController@store');

    // Profile (the currently authenticated user)
    $router->get('profile', 'ProfileController@show');
    $router->post('profile', 'ProfileController@update');
});

// Discovery
$router->group(['prefix' => '.well-known'], function () use ($router) {
    $router->get('openid-configuration', 'DiscoveryController@index');
});

// Simple health check endpoint
$router->get('/status', function () {
    return ['status' => 'good'];
});
