<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestisci prenotazione · Pescara Half Marathon</title>
    <link rel="stylesheet" href="{{ asset('css/style.css?v=1.9') }}">
</head>
<body class="center-items checkout-body">
<main class="checkout-wrapper booking-card stack-large">
    <a href="/" class="checkout-logo"><img src="/images/pescara/logo.png" alt="Pescara Half Marathon 2027"></a>
    <div>
        <h1>Gestisci la prenotazione</h1>
        <p>Inserisci il codice ricevuto via email e l'indirizzo del referente.</p>
    </div>
    @if($errors->any())<div class="notice notice-error">{{ $errors->first() }}</div>@endif
    <form action="{{ route('booking.lookup') }}" method="POST" class="stack-mid">
        @csrf
        <div class="label-box"><label>Codice prenotazione</label><input name="booking_code" value="{{ old('booking_code') }}" placeholder="MAR2027-XXXXX" required autocomplete="off"></div>
        <div class="label-box"><label>Email referente</label><input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></div>
        <button class="btn fullwidth">Apri prenotazione</button>
    </form>
    <p><a href="{{ route('booking.recovery') }}">Non trovi il codice? Recupera l'accesso con un OTP</a></p>
</main>
</body>
</html>
