<?php

use Illuminate\Support\Facades\Vite;
use JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings;

it('stamps the CSP nonce on every script tag', function () {
    Vite::useCspNonce('test-nonce');
    $settings = app(AmplitudeSettings::class);
    $settings->api_key = 'TESTAPIKEY123';
    $settings->save();
    $html = (string) view('amplitude::script')->render();

    preg_match_all('/<script\b[^>]*>/', $html, $tags);

    expect($tags[0])->not->toBeEmpty()->each->toContain('nonce="test-nonce"');
});

it('renders no nonce attribute when the app uses none', function () {
    $settings = app(AmplitudeSettings::class);
    $settings->api_key = 'TESTAPIKEY123';
    $settings->save();
    $html = (string) view('amplitude::script')->render();

    expect($html)->toContain('<script')->not->toContain('nonce=');
});
