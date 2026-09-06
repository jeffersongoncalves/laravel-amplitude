<?php

namespace JeffersonGoncalves\Amplitude;

use Illuminate\Support\Facades\Config;
use JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
