---
name: amplitude-development
description: Development guide for the laravel-amplitude package -- Amplitude Browser SDK integration for Laravel using spatie/laravel-settings.
---

# Amplitude Development Skill

## When to use this skill

- When developing or modifying the `jeffersongoncalves/laravel-amplitude` package.
- When adding new settings properties to `AmplitudeSettings`.
- When modifying the Blade tracking script or the JS config builder.
- When writing tests for the package.
- When integrating Amplitude analytics into a Laravel application.

## Setup

### Requirements

- PHP 8.2, 8.3, or 8.4
- Laravel 12 or 13
- `spatie/laravel-settings` ^3.3
- `spatie/laravel-package-tools` ^1.14.0

### Installation

```bash
composer require jeffersongoncalves/laravel-amplitude
```

### Publish and run the settings migration

```bash
php artisan vendor:publish --tag="amplitude-settings-migrations"
php artisan migrate
```

### Include the tracking script in your layout

```blade
<head>
    @include('amplitude::script')
</head>
```

## Architecture

### Directory Structure

```
src/
  AmplitudeServiceProvider.php      # Package service provider
  Settings/
    AmplitudeSettings.php           # Spatie Settings class (group: amplitude)
database/
  settings/
    2026_01_01_000000_create_amplitude_settings.php  # Settings migration
resources/
  views/
    script.blade.php                # Tracking script Blade view
```

### Service Provider

`AmplitudeServiceProvider` extends `Spatie\LaravelPackageTools\PackageServiceProvider`:

- Registers the package name as `laravel-amplitude` with views.
- Auto-registers `AmplitudeSettings` into the `settings.settings` config array.
- Registers the settings migration path in `settings.migrations_paths`.
- Publishes settings migrations under tag `amplitude-settings-migrations`.

```php
class AmplitudeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-amplitude')
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        parent::packageRegistered();

        Config::set('settings.settings', array_merge(
            Config::get('settings.settings', []),
            [AmplitudeSettings::class]
        ));
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        Config::set('settings.migrations_paths', array_merge(
            [__DIR__.'/../database/settings'],
            Config::get('settings.migrations_paths', [])
        ));

        $this->publishes([
            __DIR__.'/../database/settings' => database_path('settings'),
        ], 'amplitude-settings-migrations');
    }
}
```

### Settings Class

`AmplitudeSettings` uses `spatie/laravel-settings` with group `amplitude`:

```php
use Spatie\LaravelSettings\Settings;

class AmplitudeSettings extends Settings
{
    public ?string $api_key;
    public string $server_zone;
    public ?string $server_url;
    public ?string $custom_lib_url;
    public bool $autocapture;
    public string $identity_storage;
    public ?string $cookie_domain;
    public int $cookie_expiration;
    public bool $secure_cookie;
    public int $session_timeout_minutes;
    public ?int $min_id_length;
    public bool $opt_out;
    public bool $debug;
    public int $flush_queue_size;
    public int $flush_interval_millis;

    public static function group(): string
    {
        return 'amplitude';
    }

    public function toJsConfig(): array { /* ... */ }
}
```

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `api_key` | `?string` | `null` | Amplitude project API key |
| `server_zone` | `string` | `'US'` | `'US'` or `'EU'` |
| `server_url` | `?string` | `null` | Custom ingestion endpoint (proxy) |
| `custom_lib_url` | `?string` | `null` | Custom loader script URL |
| `autocapture` | `bool` | `true` | Automatic event capture |
| `identity_storage` | `string` | `'cookie'` | `'cookie'`, `'localStorage'`, or `'none'` |
| `cookie_domain` | `?string` | `null` | Cookie domain |
| `cookie_expiration` | `int` | `365` | Cookie expiration in days |
| `secure_cookie` | `bool` | `false` | HTTPS-only cookies |
| `session_timeout_minutes` | `int` | `30` | Session timeout in minutes |
| `min_id_length` | `?int` | `null` | Minimum ID length |
| `opt_out` | `bool` | `false` | Opt out of tracking by default |
| `debug` | `bool` | `false` | Debug logging |
| `flush_queue_size` | `int` | `30` | Batch size before auto-flush |
| `flush_interval_millis` | `int` | `1000` | Auto-flush interval in ms |

### The `toJsConfig()` Method

This method converts the settings into an array suitable for `amplitude.init()`:

```php
public function toJsConfig(): array
{
    $config = [];

    if ($this->server_url) {
        $config['serverUrl'] = $this->server_url;
    } else {
        $config['serverZone'] = $this->server_zone;
    }

    $cookieOptions = [];

    if ($this->cookie_domain) {
        $cookieOptions['domain'] = $this->cookie_domain;
    }

    $cookieOptions['expiration'] = $this->cookie_expiration;
    $cookieOptions['secure'] = $this->secure_cookie;

    $config['cookieOptions'] = $cookieOptions;
    $config['identityStorage'] = $this->identity_storage;
    $config['autocapture'] = $this->autocapture;
    $config['optOut'] = $this->opt_out;
    $config['sessionTimeout'] = $this->session_timeout_minutes * 60000;

    if ($this->min_id_length !== null) {
        $config['minIdLength'] = $this->min_id_length;
    }

    $config['flushQueueSize'] = $this->flush_queue_size;
    $config['flushIntervalMillis'] = $this->flush_interval_millis;

    return $config;
}
```

Note that `debug`/`logLevel` is intentionally *not* included here -- see the Blade View section.

### Blade View

The `script.blade.php` view resolves `AmplitudeSettings` from the container and conditionally renders the Amplitude tracking scripts:

```blade
@php
    use Illuminate\Support\Js;

    $settings = app(\JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings::class);
@endphp

@if(!empty($settings->api_key))
<script type="text/javascript" src="{{ $settings->custom_lib_url ?: 'https://cdn.amplitude.com/script/'.$settings->api_key.'.js' }}"></script>
<script type="text/javascript">
    (function () {
        var config = {!! json_encode($settings->toJsConfig(), ...) !!};
        config.logLevel = window.amplitude.Types.LogLevel.{{ $settings->debug ? 'Debug' : 'None' }};
        window.amplitude.init({{ Js::from($settings->api_key) }}, config);
    })();
</script>
@endif
```

Key behaviors:
- Only renders when `api_key` is not null/empty.
- Loads the loader script from `custom_lib_url` when set, otherwise `https://cdn.amplitude.com/script/{api_key}.js`.
- `logLevel` is set by referencing the real `window.amplitude.Types.LogLevel` JS enum (`Debug`/`None`), not a bare string -- required by the Amplitude Browser SDK v2.
- Uses `json_encode` with `JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT` for the config, and `Illuminate\Support\Js::from()` for the api key, so a malicious `api_key` (e.g. containing `</script>`) cannot break out of the script tag.

## Features

### Reading Settings

```php
use JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings;

$settings = app(AmplitudeSettings::class);

echo $settings->api_key;                  // e.g., "abc123def456"
echo $settings->debug;                    // false
echo $settings->session_timeout_minutes;  // 30
```

### Updating Settings

```php
$settings = app(AmplitudeSettings::class);
$settings->api_key = 'your-amplitude-api-key';
$settings->server_zone = 'EU';
$settings->debug = true;
$settings->save();
```

### Data Residency (EU)

```php
$settings = app(AmplitudeSettings::class);
$settings->server_zone = 'EU';
$settings->save();
```

### Custom Ingestion Endpoint / Loader URL

```php
$settings = app(AmplitudeSettings::class);
$settings->server_url = 'https://proxy.example.com';
$settings->custom_lib_url = 'https://proxy.example.com/amplitude.js';
$settings->save();
```

## Configuration

This package uses **no config file**. All configuration is managed via `spatie/laravel-settings` in the database.

### Settings Migration

The migration creates 15 settings in the `amplitude` group:

```php
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('amplitude.api_key', null);
        $this->migrator->add('amplitude.server_zone', 'US');
        $this->migrator->add('amplitude.server_url', null);
        $this->migrator->add('amplitude.custom_lib_url', null);
        $this->migrator->add('amplitude.autocapture', true);
        $this->migrator->add('amplitude.identity_storage', 'cookie');
        $this->migrator->add('amplitude.cookie_domain', null);
        $this->migrator->add('amplitude.cookie_expiration', 365);
        $this->migrator->add('amplitude.secure_cookie', false);
        $this->migrator->add('amplitude.session_timeout_minutes', 30);
        $this->migrator->add('amplitude.min_id_length', null);
        $this->migrator->add('amplitude.opt_out', false);
        $this->migrator->add('amplitude.debug', false);
        $this->migrator->add('amplitude.flush_queue_size', 30);
        $this->migrator->add('amplitude.flush_interval_millis', 1000);
    }
};
```

### Adding New Settings

When adding a new setting property:

1. Add the property to `AmplitudeSettings`.
2. Create a new settings migration adding the corresponding `amplitude.*` key.
3. Update `toJsConfig()` if the setting should be passed to `amplitude.init()`.
4. Update `script.blade.php` if the setting affects the tracking script rendering.

## Testing Patterns

### Test Setup

The package uses Pest with `pestphp/pest-plugin-laravel` and `orchestra/testbench`.

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer analyse

# Run code formatting
composer format
```

### Writing Tests

```php
use JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings;

it('renders the tracking script when api_key is set', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->api_key = 'test-key-123';
    $settings->save();

    $view = view('amplitude::script')->render();

    expect($view)->toContain('amplitude.init')
        ->toContain('test-key-123');
});

it('does not render the script when api_key is null', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->api_key = null;
    $settings->save();

    $view = view('amplitude::script')->render();

    expect($view)->not->toContain('amplitude.init');
});
```

### Testing toJsConfig()

```php
it('uses server_url instead of server_zone when set', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->server_url = 'https://proxy.example.com';

    $config = $settings->toJsConfig();

    expect($config)->toHaveKey('serverUrl')
        ->and($config)->not->toHaveKey('serverZone');
});

it('converts session_timeout_minutes to milliseconds', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->session_timeout_minutes = 45;

    expect($settings->toJsConfig()['sessionTimeout'])->toBe(2700000);
});
```

### Testing Settings Independently

```php
it('has the correct default values', function () {
    $settings = app(AmplitudeSettings::class);

    expect($settings->api_key)->toBeNull();
    expect($settings->server_zone)->toBe('US');
    expect($settings->debug)->toBeFalse();
    expect($settings->autocapture)->toBeTrue();
    expect($settings->identity_storage)->toBe('cookie');
    expect($settings->cookie_expiration)->toBe(365);
    expect($settings->session_timeout_minutes)->toBe(30);
});

it('belongs to the amplitude group', function () {
    expect(AmplitudeSettings::group())->toBe('amplitude');
});
```
