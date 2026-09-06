<div class="filament-hidden">

![Laravel Amplitude](https://raw.githubusercontent.com/jeffersongoncalves/laravel-amplitude/master/art/jeffersongoncalves-laravel-amplitude.png)

</div>

# Laravel Amplitude

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-amplitude.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-amplitude)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-amplitude/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-amplitude/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-amplitude.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-amplitude)

This Laravel package seamlessly integrates the [Amplitude Browser SDK](https://amplitude.com/docs/sdks/analytics/browser/browser-sdk-2) into your Blade templates. Easily track user interactions, page views, and product usage directly within your Laravel application, with all configuration managed via database settings using [spatie/laravel-settings](https://github.com/spatie/laravel-settings).

## Requirements

- PHP 8.2+
- Laravel 12+
- [spatie/laravel-settings](https://github.com/spatie/laravel-settings) configured (the `settings` table must exist)

## Installation

Install the package via composer:

```bash
composer require jeffersongoncalves/laravel-amplitude
```

If you haven't already, publish the `spatie/laravel-settings` migration to create the `settings` table:

```bash
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"
```

Then publish and run the Amplitude settings migration:

```bash
php artisan vendor:publish --tag=amplitude-settings-migrations
php artisan migrate
```

## Usage

Add the Amplitude script to your Blade layout (typically before `</head>`):

```blade
@include('amplitude::script')
```

### Configuring Settings

Settings are stored in the database and can be managed via code:

```php
use JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings;

$settings = app(AmplitudeSettings::class);
$settings->api_key = 'YOUR_AMPLITUDE_API_KEY';
$settings->save();
```

### Data Residency

Amplitude supports data residency in the US and EU. Set the `server_zone` accordingly:

```php
// EU Data Residency
$settings->server_zone = 'EU';
$settings->save();
```

To route ingestion through your own endpoint instead, set `server_url` (this takes precedence over `server_zone`):

```php
$settings->server_url = 'https://proxy.yourdomain.com';
$settings->save();
```

### Proxy Configuration

To load the Amplitude loader script from your own proxy/self-hosted copy instead of `https://cdn.amplitude.com/script/{api_key}.js`:

```php
$settings->custom_lib_url = 'https://proxy.yourdomain.com/amplitude.js';
$settings->save();
```

### Available Settings

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `api_key` | `?string` | `null` | Your Amplitude project API key (required for tracking) |
| `server_zone` | `string` | `'US'` | Data residency: `'US'` or `'EU'` |
| `server_url` | `?string` | `null` | Custom ingestion endpoint (proxy); overrides `server_zone` when set |
| `custom_lib_url` | `?string` | `null` | Self-hosted/proxy URL for the loader script, replacing `https://cdn.amplitude.com/script/{api_key}.js` |
| `autocapture` | `bool` | `true` | Enable Amplitude's built-in autocapture (page views, sessions, form interactions, file downloads, attribution) |
| `identity_storage` | `string` | `'cookie'` | `'cookie'`, `'localStorage'`, or `'none'` |
| `cookie_domain` | `?string` | `null` | Cookie domain, for cross-subdomain tracking |
| `cookie_expiration` | `int` | `365` | Cookie expiration in days |
| `secure_cookie` | `bool` | `false` | HTTPS-only cookies |
| `session_timeout_minutes` | `int` | `30` | Session timeout in minutes (converted to milliseconds in JS config) |
| `min_id_length` | `?int` | `null` | Minimum length for user/device IDs |
| `opt_out` | `bool` | `false` | Initialize with tracking opted out |
| `debug` | `bool` | `false` | Enable Amplitude debug logging (maps to `LogLevel.Debug`/`LogLevel.None`) |
| `flush_queue_size` | `int` | `30` | Batch size before auto-flush |
| `flush_interval_millis` | `int` | `1000` | Auto-flush interval in milliseconds |

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Goncalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
