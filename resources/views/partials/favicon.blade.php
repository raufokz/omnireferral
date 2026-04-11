@php
    $faviconAssets = [
        'favicon.ico',
        'favicon-16x16.png',
        'favicon-32x32.png',
        'apple-touch-icon.png',
        'android-chrome-192x192.png',
        'android-chrome-512x512.png',
        'site.webmanifest',
    ];

    $faviconVersion = collect($faviconAssets)
        ->map(fn ($asset) => public_path($asset))
        ->filter(fn ($path) => file_exists($path))
        ->map(fn ($path) => filemtime($path))
        ->max() ?: time();
@endphp
<link rel="icon" href="{{ asset('favicon.ico') }}?v={{ $faviconVersion }}" sizes="any">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v={{ $faviconVersion }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}?v={{ $faviconVersion }}">
<meta name="msapplication-TileColor" content="#002366">
