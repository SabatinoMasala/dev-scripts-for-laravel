<?php

namespace SabatinoMasala\DevScriptsForLaravel;

use Illuminate\Support\ServiceProvider;
use SabatinoMasala\DevScriptsForLaravel\Commands\DevServices;
use SabatinoMasala\DevScriptsForLaravel\Commands\RunProcessWithWatcher;

class DevScriptsServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/dev-services.php' => config_path('dev-services.php'),
        ], 'dev-services');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/dev-services.php', 'dev-services'
        );
        if (app()->environment() !== 'local') {
            return;
        }
        $this->commands([
            RunProcessWithWatcher::class,
            DevServices::class,
        ]);
    }
}
