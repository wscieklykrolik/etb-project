@php
    $googleAnalyticsMeasurementId = config('analytics.google.measurement_id');
    $googleAnalyticsEnabled = is_string($googleAnalyticsMeasurementId)
        && preg_match('/^G-[A-Z0-9]+$/', $googleAnalyticsMeasurementId) === 1;
@endphp

@if ($googleAnalyticsEnabled)
    <script
        type="text/plain"
        data-cookie-category="analytics"
        data-cookie-type="text/javascript"
    >
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () {
            window.dataLayer.push(arguments);
        };

        window.gtag('consent', 'default', {
            analytics_storage: 'denied',
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            personalization_storage: 'denied',
            functionality_storage: 'granted',
            security_storage: 'granted',
        });
        window.gtag('consent', 'update', {
            analytics_storage: 'granted',
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            personalization_storage: 'denied',
        });
        window.gtag('set', 'ads_data_redaction', true);
        window.gtag('js', new Date());
        window.gtag('config', @js($googleAnalyticsMeasurementId), {
            allow_google_signals: false,
            allow_ad_personalization_signals: false,
            cookie_expires: 15552000,
            debug_mode: @js((bool) config('analytics.google.debug')),
            send_page_view: true,
        });
    </script>
    <script
        type="text/plain"
        data-cookie-category="analytics"
        data-cookie-type="text/javascript"
        data-cookie-src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($googleAnalyticsMeasurementId) }}"
        async
    ></script>
@endif
