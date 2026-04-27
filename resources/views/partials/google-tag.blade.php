@php($measurementId = config('services.google_analytics.measurement_id'))

@if (!empty($measurementId))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($measurementId) }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @json($measurementId));
    </script>
@endif

