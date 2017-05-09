<?php

namespace Northstar\Providers;

use DoSomething\Gateway\Blink;
use Illuminate\Support\ServiceProvider;
use Northstar\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::creating(function ($user) {
            // Set source automatically if not provided.
            $user->source = $user->source ?: client_id();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // @TODO: This should be registered in Gateway's service provider!
        $this->app->singleton(Blink::class, function () {
            return new Blink(config('services.blink'));
        });
    }
}
