<?php

namespace Ferrisbane\AssetManager\Facades;

class AssetManager
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