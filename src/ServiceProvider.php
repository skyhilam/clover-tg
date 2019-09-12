<?php

namespace Clover\CloverTg;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/clover-tg.php';

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('clover-tg.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'clover-tg'
        );

        $this->app->bind('clover-tg', function () {
            return new CloverTg();
        });
    }
}
