<?php

namespace Northstar\Providers;

use Illuminate\Support\ServiceProvider;
use Northstar\Models\ApiKey;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * The validator instance.
     *
     * @var \Illuminate\Validation\Factory
     */
    protected $validator;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->validator = $this->app->make('validator');

        // Add custom validator for localized date (e.g. `MM/DD/YYYY` or `DD/MM/YYYY`).
        $this->validator->extend('scope', function ($attribute, $value, $parameters) {
            return ApiKey::validateScopes($value);
        }, 'Invalid scope(s) provided.');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
