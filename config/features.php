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

    'password-reset' => env('DS_ENABLE_PASSWORD_RESET'),

    'rate-limiting' => env('DS_ENABLE_RATE_LIMITING'),

];
