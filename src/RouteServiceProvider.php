<?php

namespace Ferrisbane\AssetManager;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{

    protected $namespace = 'Ferrisbane\AssetManager\Controllers';

    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            require __DIR__.'/../resources/routes.php';
        });
    }
}