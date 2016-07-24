<?php

    Route::get(config('assetmanager.route'), [
        'as' => 'assetmanager.asset',
        function($fileHash) {
            if (Cache::has($fileHash)) {
                $file = Cache::get($fileHash);

                $lifetime = 31556926;

                $etag = $file['fileHash'];
                $lastModified = $file['lastModified']->toRfc2822String();
                $expires = $file['lastModified']->addYear()->toRfc2822String();

                $headers = [
                    'Content-Disposition' => 'inline; filename="'. $fileHash .'"',
                    'Last-Modified' => $lastModified,
                    'Cache-Control' => 'must-revalidate',
                    'Expires' => $expires,
                    'Pragma' => 'public',
                    'Etag' => $etag
                ];

                $h1 = !empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lastModified;
                $h2 = !empty($_SERVER['HTTP_IF_NONE_MATCH']) && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $etag;

                // If the file is in user's browser cache, don't send file
                if ($h1 || $h2) {
                    return Response::make('', 304, $headers);
                }

                $headers = array_merge($headers, [
                    'Content-Type' => $file['contentType'],
                    'Content-Length' => $file['contentLength']
                ]);

                return Response::make($file['file'], 200, $headers);
            }

            return App::abort(404);
        }
    ]);
