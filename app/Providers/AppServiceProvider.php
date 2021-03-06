<?php

namespace Northstar\Providers;

use Northstar\Models\User;
use DoSomething\Gateway\Blink;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Mongodb\Eloquent\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::creating(function (User $user) {
            // Set source automatically if not provided.
            $user->source = $user->source ?: client_id();
        });

        // @TODO: Remove after the weekend of scripting!
        // 2017/10/27
        if (! $this->app->runningInConsole()) {
            User::created(function (User $user) {
                // Send payload to Blink for Customer.io profile.

                $blinkPayload = $user->toBlinkPayload();
                info('blink: user.create', $blinkPayload);
                if (config('features.blink')) {
                    gateway('blink')->userCreate($blinkPayload);
                }

                // Send metrics to StatHat.
                app('stathat')->ezCount('user created');
                app('stathat')->ezCount('user created - '.$user->source);
            });
        }

        User::updating(function (User $user) {
            // Write profile changes to the log, with redacted values for hidden fields.
            $changed = array_replace_keys($user->getDirty(), $user->getHidden(), '*****');
            logger('updated user', ['id' => $user->id, 'client_id' => client_id(), 'changed' => $changed]);
        });

        // @TODO: Remove after the weekend of scripting!
        // 2017/10/27
        if (! $this->app->runningInConsole()) {
            User::updated(function (User $user) {
                // Send payload to Blink for Customer.io profile.
                $blinkPayload = $user->toBlinkPayload();
                info('blink: user.update', $blinkPayload);
                if (config('features.blink') && config('features.blink-updates')) {
                    gateway('blink')->userCreate($blinkPayload);
                }
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Fix missing method in laravel-mongodb.
        Builder::macro('getName', function () {
            return 'mongodb';
        });
    }
}
