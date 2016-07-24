# Asset Manager

A PHP package that manages assets like css, js, images, fonts etc..


- [Installation](#installation)

## Installation
This package requires PHP 5.4+ and includes laravel support.

To install through composer you can either use `composer require ferrisbane/assetmanager` or include the package in your `composer.json`.

```php
"ferrisbane/assetmanager": "0.1.*"
```

Then run either `composer install` or `composer update` to download the package.

To publish the required config for assetmanager use:
`php artisan config:publish --path=vendor/ferrisbane/assetmanager/config ferrisbane/assetmanager`

### Customisation
The assetmanager config: `config/assetmanager.php` allows you customise the route url of assets.
Define custom version overrides of assets
And to enable/disable external asset catching and minutes assets are cached before attempting to reacquire any newer versions of external (any file located outside of the current domain)