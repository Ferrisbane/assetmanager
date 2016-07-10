<?php

namespace Ferrisbane\AssetManager;

use Ferrisbane\AssetManager\Contracts\AssetManager as AssetManagerContract;
use Exception;
use Response;
use GuzzleHttp;
use Cache;
use Carbon\Carbon;

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

                // Cache::forget(md5($path));
                $file = Cache::remember(md5($path), $this->config['external']['catch_minutes'], function() use($path)
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

            } else {
                $file = $this->downloadFile($path);
            }

            return $this->getRoute(route('assetmanager.asset', [
                $file['pathHash']
            ]), $file['fileHash']);
        }


        return $this->getRoute($path);
    }

    public function downloadFile($url)
    {
        $client = new GuzzleHttp\Client();

        $request = $client->get($url, ['stream' => true]);

        if ($request->getStatusCode() == 200) {
            $stream = $request->getBody();
            $contentType = $request->getHeader('content-type')[0];

            return [
                'file' => '/** Downloaded on '. Carbon::now() .' **/ ' . $stream->getContents(),
                'contentType' => $contentType
            ];
        }

        return null;
    }

    public function getRoute($path, $version = false)
    {
        $bestMatch = false;
        foreach ($this->config['version_overrides'] as $match => $versionNumber) {
            if (strpos($match, '*')) {
                $regexArray = explode('!\e/!', str_replace(['/','.'], '!\e/!', $match));
                $pathArray = explode('!\e/!', str_replace(['/','.'], '!\e/!', $path));

                foreach ($regexArray as $key => $part) {
                    if ($part != $pathArray[$key] && $part != '*') {
                        break;
                    } elseif ($part == '*') {
                        $matchLength = count($regexArray);
                        if ($bestMatch < $matchLength) {
                            $bestMatch = $matchLength;
                            $version = $versionNumber;
                        }
                    }
                }
            } else {
                if ($path == $match) {
                    $version = $versionNumber;
                    break;
                }
            }
        }

        if ($version) {
            $path .= '?v='.$version;
        }
        
        return $path;
    }

}