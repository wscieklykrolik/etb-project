@php
    $analyticsMeasurementId = config('analytics.google.measurement_id');
    $analyticsIsEnabled = is_string($analyticsMeasurementId)
        && preg_match('/^G-[A-Z0-9]+$/', $analyticsMeasurementId) === 1;
@endphp

@if ($analyticsIsEnabled)
    <script type="text/plain" data-cookie-category="analytics" data-cookie-type="text/javascript">
        window.etbAnalytics.track(@js($eventName), @js($eventParameters ?? []));
    </script>
@endif
