<?php

namespace Ferrisbane\AssetManager;

use Ferrisbane\AssetManager\Contracts\AssetManager as AssetManagerContract;
use Exception;
use Response;
use GuzzleHttp;
use Cache;

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
    }

    public function asset($path)
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {

            if ($this->config['external']['catch']) {

                Cache::forget(md5($path));
                $file = Cache::remember(md5($path), $this->config['external']['catchminutes'], function() use($path)
                {
                    $fileData = $this->downloadFile($path);

                    if ($fileData) {
                        return [
                            'file' => $fileData['file'],
                            'fileHash' => md5($fileData['file']),
                            'contentType' => $fileData['contentType'],
                            'path' => $path,
                            'pathHash' => md5($path)
                        ];
                    }
                });

                return $this->getRoute(route('assetmanager.asset', [
                    $file['pathHash'],
                    $file['contentType']
                ]), $file['fileHash']);

            } else {
                return $this->downloadFile($path);
            }

            return $path;
        }

        return $path;
    }

    public function downloadFile($url)
    {
        $client = new GuzzleHttp\Client();

        $request = $client->get($url, ['stream' => true]);

        if ($request->getStatusCode() == 200) {
            $stream = $request->getBody();

            $contentType = $request->getHeader('content-type')[0];
            $contentType = explode('/', $contentType)[1];
            $test = explode('; ', $contentType);

            if ( ! empty($test)) {
                $contentType = $test[0];
            }

            // preg_match_all('/(?<=(text\/))(\w*)/', $test, $contentType);

            return [
                'file' => $stream->getContents(),
                'contentType' => $contentType
            ];
        }

        return null;
        /*,
        [
            'headers' => ['key'=>'value'],
            'query'   => ['param'=>'value'],
            'auth'    => ['username', 'password'],
            'save_to' => '/path/to/local.file',
        ]);*/
    }

    public function getRoute($path, $version = false)
    {
        if ($version) {
            $path .= '?v='.$version;
        }
        
        return $path;
    }

}