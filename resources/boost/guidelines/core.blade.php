## Laravel Amplitude

### Overview

Laravel package that integrates the Amplitude Browser SDK (v2) into Blade templates using `spatie/laravel-settings` for database-stored configuration. Loads the Amplitude loader script (`https://cdn.amplitude.com/script/{api_key}.js`) and initializes it with settings managed via the `AmplitudeSettings` class.

**Namespace:** `JeffersonGoncalves\Amplitude`
**Service Provider:** `AmplitudeServiceProvider` (extends `Spatie\LaravelPackageTools\PackageServiceProvider`)

### Key Concepts

- **Settings-driven**: All configuration lives in `AmplitudeSettings` (group: `amplitude`), not in config files.
- **Blade view**: Include `amplitude::script` in your layout to render the Amplitude loader script and `amplitude.init()` call.
- **JS config builder**: `AmplitudeSettings::toJsConfig()` converts settings to a JavaScript-compatible configuration array.
- **Auto-discovery**: Service provider is auto-discovered via `composer.json` extra.laravel.providers.
- **Custom lib URL**: Supports loading the Amplitude loader script from a custom/proxy URL via `custom_lib_url`.

### Settings (spatie/laravel-settings)

Settings class: `JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings`
Group: `amplitude`

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `api_key` | `?string` | `null` | Amplitude project API key (required for tracking) |
| `server_zone` | `string` | `'US'` | Data residency: `'US'` or `'EU'` |
| `server_url` | `?string` | `null` | Custom ingestion endpoint (proxy); overrides `server_zone` when set |
| `custom_lib_url` | `?string` | `null` | Custom loader script URL |
| `autocapture` | `bool` | `true` | Enable Amplitude autocapture |
| `identity_storage` | `string` | `'cookie'` | `'cookie'`, `'localStorage'`, or `'none'` |
| `cookie_domain` | `?string` | `null` | Cookie domain for cross-subdomain tracking |
| `cookie_expiration` | `int` | `365` | Cookie expiration in days |
| `secure_cookie` | `bool` | `false` | HTTPS-only cookies |
| `session_timeout_minutes` | `int` | `30` | Session timeout (converted to ms in JS config) |
| `min_id_length` | `?int` | `null` | Minimum length for user/device IDs |
| `opt_out` | `bool` | `false` | Initialize with tracking opted out |
| `debug` | `bool` | `false` | Maps to JS `LogLevel.Debug`/`LogLevel.None` |
| `flush_queue_size` | `int` | `30` | Batch size before auto-flush |
| `flush_interval_millis` | `int` | `1000` | Auto-flush interval in ms |

@verbatim
<code-snippet name="read-settings" lang="php">
use JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings;

$settings = app(AmplitudeSettings::class);
$settings->api_key;       // ?string
$settings->debug;         // bool
$settings->toJsConfig();  // array (JS-compatible config)
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="update-settings" lang="php">
use JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings;

$settings = app(AmplitudeSettings::class);
$settings->api_key = 'your-api-key';
$settings->server_zone = 'EU';
$settings->debug = true;
$settings->save();
</code-snippet>
@endverbatim

### Configuration

No config file is published. All configuration is managed through the `AmplitudeSettings` class.

**Publish settings migration:**

@verbatim
<code-snippet name="publish-migration" lang="bash">
php artisan vendor:publish --tag="amplitude-settings-migrations"
php artisan migrate
</code-snippet>
@endverbatim

### Blade Integration

Include the tracking script in your layout's `<head>`:

@verbatim
<code-snippet name="blade-include" lang="blade">
<head>
    @include('amplitude::script')
</head>
</code-snippet>
@endverbatim

The script loads the Amplitude loader (or `custom_lib_url`) and calls `amplitude.init()` with the api key and JS config. It only renders when `api_key` is not empty.

### Conventions

- Settings group name: `amplitude`
- View namespace: `amplitude`
- Package name: `laravel-amplitude`
- Migration publish tag: `amplitude-settings-migrations`
- `toJsConfig()` omits `serverZone` when `server_url` is set (uses `serverUrl` instead), omits `minIdLength` when null, and omits `cookieOptions.domain` when `cookie_domain` is null.
- `session_timeout_minutes` is converted to milliseconds (`sessionTimeout`) in the JS config.
- `debug` is not part of `toJsConfig()` -- the Blade view special-cases it to reference the `window.amplitude.Types.LogLevel` JS enum (`Debug`/`None`), not a plain string.
- No models or relationships -- this is a script-injection package.
- PHP 8.2+ required, Laravel 12+, spatie/laravel-settings ^3.3.
