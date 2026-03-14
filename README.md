# PageSpeed with History

[![Latest Version on Packagist](https://img.shields.io/packagist/v/grezlikowski/page-speed-with-history.svg?style=flat-square)](https://packagist.org/packages/grezlikowski/page-speed-with-history)
[![Tests](https://img.shields.io/github/actions/workflow/status/grezlikowski/page-speed-with-history/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/grezlikowski/page-speed-with-history/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/grezlikowski/page-speed-with-history.svg?style=flat-square)](https://packagist.org/packages/grezlikowski/page-speed-with-history)

PageSpeed with History is a PHP package that provides a convenient way to track and analyze the performance of web pages over time. It allows developers to monitor page speed metrics, identify performance bottlenecks, and optimize their websites for better user experience.

## Installation

You can install the package via composer:

```bash
composer require grezlikowski/page-speed-with-history
```

Publish the config file and migration:

```bash
php artisan vendor:publish --tag="page-speed-config"
php artisan vendor:publish --tag="page-speed-migrations"
php artisan migrate
```

Add your Google PageSpeed Insights API key to `.env`:

```env
GOOGLE_PAGESPEED_API_KEY=your-api-key-here
```

## Usage

Visit `/page-speed` in your browser. By default, the panel is only accessible in `local` environment.

### Authorization

To control access in production, define authorization logic in your `AppServiceProvider`:

```php
use Grezlikowski\PageSpeed\PageSpeedPanel;

public function boot(): void
{
    PageSpeedPanel::auth(function ($request) {
        return in_array($request->user()?->email, [
            'admin@example.com',
        ]);
    });
}
```

Or check for a role/permission:

```php
PageSpeedPanel::auth(function ($request) {
    return $request->user()?->hasRole('administrator');
});
```

### Configuration

You can customize the panel path, middleware, and other settings in `config/page-speed.php`:

```php
return [
    'api_key' => env('GOOGLE_PAGESPEED_API_KEY', ''),
    'path' => env('PAGESPEED_PATH', 'page-speed'),
    'middleware' => ['web'],
    'default_strategy' => 'mobile',
    'history_limit' => 50,
    'timeout' => 60,
    'enabled' => env('PAGESPEED_ENABLED', true),
];
```

### Publishing Views

To customize the views:

```bash
php artisan vendor:publish --tag="page-speed-views"
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Łukasz Gręźlikowski](https://github.com/grezlikowski)
- [All Contributors](../../contributors)

This package was created using the [Spatie Package Skeleton](https://github.com/spatie/package-skeleton-php).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
