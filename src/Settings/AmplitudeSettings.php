<?php

namespace JeffersonGoncalves\Amplitude\Settings;

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
}
