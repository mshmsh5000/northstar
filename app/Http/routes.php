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
    $router->get('users/{term}/{id}/campaigns', 'CampaignController@index');

    // Profile (the currently authenticated user)
    $router->get('profile', 'ProfileController@show');
    $router->post('profile', 'ProfileController@update');

    $router->get('user/campaigns/{campaign_id}', 'CampaignController@show');
    $router->post('user/campaigns/{campaign_id}/signup', 'CampaignController@signup');
    $router->post('user/campaigns/{campaign_id}/reportback', 'CampaignController@reportback');
    $router->put('user/campaigns/{campaign_id}/reportback', 'CampaignController@reportback');

    // Kudos
    $router->post('kudos', 'KudosController@store');
    $router->delete('kudos', 'KudosController@delete');

    // API Keys
    $router->resource('keys', 'KeyController');
    $router->get('scopes', function () {
        return \Northstar\Models\ApiKey::scopes();
    });
});
