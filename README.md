# Asset Manager

A PHP package that manages assets like css, js etc.


- [Installation](#installation)

## Installation
This package requires PHP 5.4+ and includes laravel support.

To install through composer you can either use `composer require ferrisbane/assetmanager` or include the package in your `composer.json`.

```php
"ferrisbane/assetmanager": "0.1.*"
```

Then run either `composer install` or `composer update` to download the package.

Once installed add this to your routes file

```php
	Route::get('/asset/{fileHash}', [
		'as' => 'assetmanager.asset',
		function($fileHash) {
			if (Cache::has($fileHash)) {
				$file = Cache::get($fileHash);
				$response = Response::make($file['file']);
			    $response->header('Content-Type', $file['contentType']);

			    return $response;
			}

			return App::abort(404);
		}
	]);
```