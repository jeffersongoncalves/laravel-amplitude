<?php

namespace JeffersonGoncalves\Amplitude\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\Amplitude\AmplitudeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelSettings\LaravelSettingsServiceProvider;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->seedSettings();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelSettingsServiceProvider::class,
            AmplitudeServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group');
            $table->string('name');
            $table->boolean('locked')->default(false);
            $table->json('payload');
            $table->timestamps();
            $table->unique(['group', 'name']);
        });
    }

    protected function seedSettings(): void
    {
        $migrator = app(SettingsMigrator::class);

        $migrator->add('amplitude.api_key', null);
        $migrator->add('amplitude.server_zone', 'US');
        $migrator->add('amplitude.server_url', null);
        $migrator->add('amplitude.custom_lib_url', null);
        $migrator->add('amplitude.autocapture', true);
        $migrator->add('amplitude.identity_storage', 'cookie');
        $migrator->add('amplitude.cookie_domain', null);
        $migrator->add('amplitude.cookie_expiration', 365);
        $migrator->add('amplitude.secure_cookie', false);
        $migrator->add('amplitude.session_timeout_minutes', 30);
        $migrator->add('amplitude.min_id_length', null);
        $migrator->add('amplitude.opt_out', false);
        $migrator->add('amplitude.debug', false);
        $migrator->add('amplitude.flush_queue_size', 30);
        $migrator->add('amplitude.flush_interval_millis', 1000);
    }
}
