<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | This file is a custom addition to Northstar for storing feature flags, so
    | features can be conditionally toggled on and off per environment.
    |
    */

    // Enable OAuth2 endpoints & middleware. Requires PHP 5.6+.
    'oauth' => env('NORTHSTAR_ENABLE_OAUTH', false),

];
