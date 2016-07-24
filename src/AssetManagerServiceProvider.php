<?php

namespace Ferrisbane\AssetManager;

use Illuminate\Support\ServiceProvider;

class AssetManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Set the files to publish
        $this->publishes([
            __DIR__ . '/../config/assetmanager.php' => config_path('assetmanager.php')
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $config = $this->getConfig();

        $this->app->singleton('Ferrisbane\AssetManager\Contracts\AssetManager', function ($app) use ($config) {
            return new AssetManager(
                $config
            );
        });

        $this->app->alias('Ferrisbane\AssetManager\Contracts\AssetManager', 'assetmanager');

        $this->app['router']->group(['namespace' => 'Ferrisbane\AssetManager\Controllers'], function () {
            require __DIR__.'/routes.php';
        });
    }

    /**
     * Get the package config.
     *
     * @return array
     */
    protected function getConfig()
    {
        return config('assetmanager');
    }
}