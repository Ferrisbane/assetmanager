<?php

namespace Ferrisbane\AssetManager;

use Exception;

class AssetManager implements AssetManagerContract
{

    /**
     * The package configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Asset Manager constructor.
     *
     * @param Store      $store
     * @param Renderer   $renderer
     * @param Dispatcher $dispatcher
     * @param array      $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
dd($this->config);

    }

}