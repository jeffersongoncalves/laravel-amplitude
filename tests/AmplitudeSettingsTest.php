<?php

use JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings;

it('can resolve AmplitudeSettings from the container', function () {
    expect(app(AmplitudeSettings::class))->toBeInstanceOf(AmplitudeSettings::class);
});

it('belongs to the amplitude group', function () {
    expect(AmplitudeSettings::group())->toBe('amplitude');
});

it('has correct default values', function () {
    $settings = app(AmplitudeSettings::class);

    expect($settings->api_key)->toBeNull()
        ->and($settings->server_zone)->toBe('US')
        ->and($settings->server_url)->toBeNull()
        ->and($settings->custom_lib_url)->toBeNull()
        ->and($settings->autocapture)->toBeTrue()
        ->and($settings->identity_storage)->toBe('cookie')
        ->and($settings->cookie_domain)->toBeNull()
        ->and($settings->cookie_expiration)->toBe(365)
        ->and($settings->secure_cookie)->toBeFalse()
        ->and($settings->session_timeout_minutes)->toBe(30)
        ->and($settings->min_id_length)->toBeNull()
        ->and($settings->opt_out)->toBeFalse()
        ->and($settings->debug)->toBeFalse()
        ->and($settings->flush_queue_size)->toBe(30)
        ->and($settings->flush_interval_millis)->toBe(1000);
});

it('uses server_zone in config when server_url is not set', function () {
    $config = app(AmplitudeSettings::class)->toJsConfig();

    expect($config)->toHaveKey('serverZone')
        ->and($config['serverZone'])->toBe('US')
        ->and($config)->not->toHaveKey('serverUrl');
});

it('uses server_url in config and omits server_zone when server_url is set', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->server_url = 'https://proxy.example.com';
    $settings->save();

    $config = $settings->toJsConfig();

    expect($config)->toHaveKey('serverUrl')
        ->and($config['serverUrl'])->toBe('https://proxy.example.com')
        ->and($config)->not->toHaveKey('serverZone');
});

it('omits cookie domain from config when null', function () {
    $config = app(AmplitudeSettings::class)->toJsConfig();

    expect($config['cookieOptions'])->not->toHaveKey('domain');
});

it('includes cookie domain in config when set', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->cookie_domain = '.example.com';
    $settings->save();

    $config = $settings->toJsConfig();

    expect($config['cookieOptions'])->toHaveKey('domain')
        ->and($config['cookieOptions']['domain'])->toBe('.example.com');
});

it('always includes expiration and secure in cookieOptions', function () {
    $config = app(AmplitudeSettings::class)->toJsConfig();

    expect($config['cookieOptions']['expiration'])->toBe(365)
        ->and($config['cookieOptions']['secure'])->toBeFalse();
});

it('converts session_timeout_minutes to milliseconds', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->session_timeout_minutes = 45;
    $settings->save();

    expect($settings->toJsConfig()['sessionTimeout'])->toBe(45 * 60000);
});

it('omits min_id_length from config when null', function () {
    $config = app(AmplitudeSettings::class)->toJsConfig();

    expect($config)->not->toHaveKey('minIdLength');
});

it('includes min_id_length in config when set', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->min_id_length = 5;
    $settings->save();

    expect($settings->toJsConfig()['minIdLength'])->toBe(5);
});

it('does not render the script when api_key is empty', function () {
    $view = view('amplitude::script')->render();

    expect($view)->not->toContain('amplitude.init');
});

it('renders the script when api_key is set', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->api_key = 'TESTAPIKEY123';
    $settings->save();

    $view = view('amplitude::script')->render();

    expect($view)
        ->toContain('amplitude.init')
        ->toContain('TESTAPIKEY123');
});

it('escapes a malicious api_key in the rendered script', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->api_key = '</script><script>alert(1)</script>';
    $settings->save();

    $view = view('amplitude::script')->render();

    expect($view)->not->toContain('<script>alert(1)</script>');
});
