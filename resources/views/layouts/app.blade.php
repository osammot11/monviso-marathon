<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Pescara Half Marathon 2027' }}</title>
    <meta name="description" content="{{ $description ?? 'Pescara Half Marathon 2027: mezza maratona, 10 km e passeggiata sul lungomare di Pescara.' }}">
    @isset($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endisset
    <link rel="icon" type="image/jpeg" href="/images/pescara/favicon.jpg">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? 'Pescara Half Marathon 2027' }}">
    <meta property="og:description" content="{{ $description ?? 'Pescara Half Marathon 2027: mezza maratona, 10 km e passeggiata sul lungomare di Pescara.' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:image" content="{{ url('/images/pescara/logo.png') }}">
    <meta property="og:locale" content="it_IT">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'Pescara Half Marathon 2027' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Pescara Half Marathon 2027: mezza maratona, 10 km e passeggiata sul lungomare di Pescara.' }}">
    <meta name="twitter:image" content="{{ url('/images/pescara/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/style.css?v=62">
    <link rel="stylesheet" href="/iscritti-home.css?v=1.3">
    <link rel="stylesheet" href="/pescara.css?v=1.0">
    @stack('head')
</head>

<body class="{{ $bodyClass ?? '' }}">
    <div class="site-header">        
        @include('partials.navbar')
    </div>

    @yield('content')

    @include('partials.footer')
    @include('partials.cookie-banner')

    @stack('scripts')
</body>
</html>
