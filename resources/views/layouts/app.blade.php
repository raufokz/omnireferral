<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#002366">

    <title>{{ $meta['title'] ?? 'OmniReferral | Top-Tier Real Estate Referral Platform' }}</title>
    <meta name="description" content="{{ $meta['description'] ?? 'Connect with elite real estate agents using the modern referral workflow trusted by top US teams. Premium ISA-qualified leads and automated routing.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;600;700;800&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    @if(request()->routeIs('login', 'register', 'password.*'))
        <link rel="stylesheet" href="{{ asset('css/auth-custom.css') }}">
    @endif
    @php
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'OmniReferral',
            'url' => url('/'),
            'logo' => asset('images/logo.png'),
            'sameAs' => [
                'https://facebook.com/omnireferral',
                'https://twitter.com/omnireferral',
                'https://linkedin.com/company/omnireferral',
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
</head>
@php
    $bodyClass = collect([
        request()->routeIs('home') ? 'page-home' : null,
        request()->routeIs('dashboard*', 'admin.*') ? 'page-dashboard' : null,
        request()->routeIs('login', 'register', 'password.*') ? 'page-auth' : null,
    ])->filter()->implode(' ');
@endphp
<body class="antialiased {{ $bodyClass }}" data-google-maps-api-key="{{ config('services.google_maps.key') }}">


    @include('partials.header')

    <main id="main-content">
        @if (session('info'))
            <div class="app-flash app-flash--info" role="alert">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z"></path></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif
        @if (session('success'))
            <div class="app-flash" role="alert">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="app-flash app-flash--error" role="alert">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="app-flash app-flash--error" role="alert">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"></path></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 800,
                    easing: 'ease-out-cubic',
                    once: true,
                    offset: 50
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

