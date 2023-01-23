<?php

namespace Seobrain\SnowflakeSqlapiLaravel\Providers;

use Illuminate\Support\ServiceProvider;

class SnowflakeProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('snowflakeapi.php'),
            ], 'config');
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'snowflakeapi');
    }
}
