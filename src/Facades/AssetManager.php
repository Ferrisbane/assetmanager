<?php

namespace Ferrisbane\AssetManager\Facades;

use Illuminate\Support\Facades\Facade;

class AssetManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'assetmanager';
    }
}