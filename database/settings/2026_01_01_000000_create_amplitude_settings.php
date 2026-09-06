<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

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
