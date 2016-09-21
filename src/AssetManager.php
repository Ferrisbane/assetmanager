<?php

namespace Ferrisbane\AssetManager;

use Ferrisbane\AssetManager\Contracts\AssetManager as AssetManagerContract;
use Exception;
use Response;
use Storage;
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
                $file = Cache::rememberForever(md5($path), function() use($path)
                {
                    $fileData = $this->downloadFile($path);

                    if ($fileData) {
                        return array_merge($fileData, ['expireAt' => Carbon::now()->addSeconds($this->config['external']['catch_minutes'])]);
                    }
                });

                if ( ! $file) {
                    return false;
                }

                // If the file has expired try to download file again or return old file
                if (Carbon::now()->gte($file['expireAt'])) {
                    $fileData = $this->downloadFile($path);

                    if ($fileData) {
                        $file = array_merge($fileData, ['expireAt' => Carbon::now()->addSeconds($this->config['external']['catch_minutes'])]);

                        Cache::forever(md5($path), $file);
                    }
                }

            } else {
                $file = $this->downloadFile($path);
            }

            return $this->getRoute(route('assetmanager.asset', [
                $file['pathHash']
            ]), $file['fileHash']);
        }

        return $this->getRoute($path);
    }

    public function storage($path)
    {
        if ($this->config['external']['catch']) {

           Cache::forget(md5($path));
            $file = Cache::rememberForever(md5($path), function() use($path)
            {
                $fileData = $this->getFileFromStorage($path);

                if ($fileData) {
                    return array_merge($fileData, ['expireAt' => Carbon::now()->addSeconds($this->config['external']['catch_minutes'])]);
                }
            });

            if ( ! $file) {
                return false;
            }

            // If the file has expired try to download file again or return old file
            if (Carbon::now()->gte($file['expireAt'])) {
                $fileData = $this->getFileFromStorage($path);

                if ($fileData) {
                    $file = array_merge($fileData, ['expireAt' => Carbon::now()->addSeconds($this->config['external']['catch_minutes'])]);

                    Cache::forever(md5($path), $file);
                }
            }
        } else {
            $file = $this->downloadFile($path);
        }

        return $this->getRoute(route('assetmanager.asset', [
            $file['pathHash']
        ]), $file['fileHash']);
    }

    public function getFileFromStorage($path)
    {
        $content = Storage::disk('local')->get($path);
        $mime = Storage::mimeType($path);
        $paths = explode('/', $path);
        $arraySize = sizeof($paths) - 1;

        $now = Carbon::now();

        return [
            'file' => $content,
            'contentLength' => strlen($content),
            'contentType' => $mime,
            'lastModified' => $now,
            'fileHash' => md5($content),
            'fileName' => $paths[$arraySize],
            'path' => $path,
            'pathHash' => md5($path)
        ];
    }

    public function downloadFile($url)
    {
        try {
            $client = new GuzzleHttp\Client();

            $request = $client->get($url, ['stream' => true]);

            if ($request->getStatusCode() == 200) {
                $stream = $request->getBody();
                $contentType = $request->getHeader('content-type')[0];

                $file = $stream->getContents();
                $now = Carbon::now();

                if ($this->config['external']['add_header']) {
                    $content = '/** Downloaded on '. $now .' **/'. $file;
                } else {
                    $content = $file;
                }

                return [
                    'file' => $content,
                    'contentLength' => strlen($content),
                    'contentType' => $contentType,
                    'lastModified' => $now,
                    'fileHash' => md5($file),
                    'path' => $url,
                    'pathHash' => md5($url)
                ];
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    public function setConfig($key, $value)
    {
        array_set($this->config, $key, $value);

        return $this;
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
