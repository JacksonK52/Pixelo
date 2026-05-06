<?php

namespace pixelo;

use Illuminate\Support\ServiceProvider;
use pixelo\Services\AvatarGenerator;
use pixelo\BladeDirective;

class PixeloServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/avatar.php', 'avatar');

        $this->app->singleton(AvatarGenerator::class, function ($app) {
            return new AvatarGenerator($app['config']['avatar']);
        });

        $this->app->alias(AvatarGenerator::class, 'avatar');
    }

    public function boot(): void
    {
        if($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/avatar.php' => config_path('avatar.php'),
            ], 'avarat-config');
        }

        $this->loadRoutesFrom(__DIR__.'/routes/avatar.php');

        BladeDirective::register();
    }
}