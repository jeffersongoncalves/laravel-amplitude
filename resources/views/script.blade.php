@php
    $cookieConsent = function_exists('cookie_consent_script_attributes') ? cookie_consent_script_attributes('amplitude') : '';
@endphp
@php
    use Illuminate\Support\Js;

    $settings = app(\JeffersonGoncalves\Amplitude\Settings\AmplitudeSettings::class);
@endphp

@if(!empty($settings->api_key))
<script{!! $cookieConsent !!} @if(\Illuminate\Support\Facades\Vite::cspNonce()) nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" @endif src="{{ $settings->custom_lib_url ?: 'https://cdn.amplitude.com/script/'.$settings->api_key.'.js' }}"></script>
<script{!! $cookieConsent !!} @if(\Illuminate\Support\Facades\Vite::cspNonce()) nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" @endif>
    (function () {
        var config = {!! json_encode($settings->toJsConfig(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!};
        config.logLevel = window.amplitude.Types.LogLevel.{{ $settings->debug ? 'Debug' : 'None' }};
        window.amplitude.init({{ Js::from($settings->api_key) }}, config);
    })();
</script>
@endif
