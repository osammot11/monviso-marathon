<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? '100 Miglia Monviso' }}</title>
    <meta name="description" content="{{ $description ?? '100 Miglia Monviso: trail running tra Valle Po, Valle Bronda e Valle Varaita.' }}">
    @isset($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endisset
    <link rel="icon" type="image/png" href="/images/monviso/logo.png">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? '100 Miglia Monviso' }}">
    <meta property="og:description" content="{{ $description ?? '100 Miglia Monviso: trail running tra Valle Po, Valle Bronda e Valle Varaita.' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:image" content="{{ url('/images/monviso/hero.jpg') }}">
    <meta property="og:locale" content="it_IT">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? '100 Miglia Monviso' }}">
    <meta name="twitter:description" content="{{ $description ?? '100 Miglia Monviso: trail running tra Valle Po, Valle Bronda e Valle Varaita.' }}">
    <meta name="twitter:image" content="{{ url('/images/monviso/hero.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800;900&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/style.css?v=62">
    <link rel="stylesheet" href="/iscritti-home.css?v=1.3">
    <link rel="stylesheet" href="/monviso.css?v=1.0">
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
