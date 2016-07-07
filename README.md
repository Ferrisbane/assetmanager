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
	Route::get('/asset/{fileHash}.{fileType}', [
		'as' => 'assetmanager.asset',
		function($fileHash, $fileType) {
			if (Cache::has($fileHash)) {
				$response = Response::make(Cache::get($fileHash)['file']);
			    $response->header('Content-Type', 'text/'.$fileType);

			    return $response;
			}

			return App::abort(404);
		}
	]);
```