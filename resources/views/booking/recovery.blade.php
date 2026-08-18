<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recupera prenotazione · Pescara Half Marathon</title>
    <link rel="stylesheet" href="{{ asset('css/style.css?v=1.9') }}">
</head>
<body class="center-items checkout-body">
<main class="checkout-wrapper booking-card stack-large">
    <a href="/" class="checkout-logo"><img src="/images/pescara/logo.png" alt="Pescara Half Marathon 2027"></a>
    <div><h1>Recupera l'accesso</h1><p>Se l'email corrisponde a una prenotazione, invieremo un codice valido per 10 minuti.</p></div>
    @if($errors->any())<div class="notice notice-error">{{ $errors->first() }}</div>@endif
    <form action="{{ route('booking.otp.send') }}" method="POST" class="stack-mid">
        @csrf
        <div class="label-box"><label>Email referente</label><input type="email" name="email" value="{{ old('email', session('recovery_email')) }}" required></div>
        <button class="btn fullwidth">Invia codice</button>
    </form>
    @if(session('otp_sent'))
        <div class="notice">Se l'indirizzo è registrato, il codice è stato inviato.</div>
        <form action="{{ route('booking.otp.verify') }}" method="POST" class="stack-mid">
            @csrf
            <input type="hidden" name="email" value="{{ session('recovery_email') }}">
            <div class="label-box"><label>Codice a 6 cifre</label><input name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code"></div>
            <button class="btn fullwidth">Verifica e accedi</button>
        </form>
    @endif
    <p><a href="{{ route('booking.index') }}">Torna all'accesso con codice prenotazione</a></p>
</main>
</body>
</html>
