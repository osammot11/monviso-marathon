<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenotazione {{ $order->booking_code }} · Pescara Half Marathon</title>
    <link rel="stylesheet" href="{{ asset('css/style.css?v=1.9') }}">
</head>
<body class="checkout-body">
<main class="booking-page stack-large">
    <div class="booking-header">
        <a href="/" class="checkout-logo"><img src="/images/pescara/logo.png" alt="Pescara Half Marathon 2027"></a>
        <div><small>PRENOTAZIONE</small><h1>{{ $order->booking_code }}</h1><p>{{ $order->payment_contact_name }} · {{ $order->tickets->count() }} partecipanti</p></div>
        @php($statusLabels = ['awaiting_transfer' => 'In attesa del bonifico', 'transfer_review' => 'In verifica', 'paid' => 'Pagato', 'expired' => 'Scaduto', 'cancelled' => 'Annullato'])
        <span class="status-badge status-{{ $order->status }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
    </div>
    @if(session('success'))<div class="notice notice-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="notice notice-error">{{ $errors->first() }}</div>@endif

    @if($order->status === 'paid')
        <section class="booking-panel"><h2>Pagamento confermato</h2><p>I biglietti sono attivi. Puoi scaricarli qui sotto e conservarne il QR Code.</p></section>
    @elseif($order->status === 'expired')
        <section class="booking-panel"><h2>Prenotazione scaduta</h2><p>I posti riservati sono stati liberati. Se il bonifico è stato eseguito entro la scadenza, contatta l'organizzazione: l'estratto conto può ancora riattivare automaticamente l'ordine.</p></section>
    @elseif($order->status === 'cancelled')
        <section class="booking-panel"><h2>Prenotazione annullata</h2><p>Questa prenotazione non è più attiva.</p></section>
    @else
        <section class="booking-panel transfer-grid">
            <div class="stack-mid">
                <div><h2>Completa il bonifico</h2><p>Usa esattamente importo e causale. La ricevuta non è obbligatoria.</p></div>
                <dl class="bank-details">
                    <div><dt>Beneficiario</dt><dd><span id="holder">{{ $bankSettings['account_holder'] }}</span> <button type="button" class="copy-btn" data-copy="holder">Copia</button></dd></div>
                    <div><dt>IBAN</dt><dd><span id="iban">{{ $bankSettings['iban'] }}</span> <button type="button" class="copy-btn" data-copy="iban">Copia</button></dd></div>
                    @if($bankSettings['bic'])<div><dt>BIC</dt><dd>{{ $bankSettings['bic'] }}</dd></div>@endif
                    <div><dt>Importo</dt><dd><strong id="amount">€ {{ number_format($order->total_amount, 2, ',', '.') }}</strong> <button type="button" class="copy-btn" data-copy-value="{{ number_format($order->total_amount, 2, '.', '') }}">Copia</button></dd></div>
                    <div><dt>Causale univoca</dt><dd><strong id="reference">{{ $order->bank_reference }}</strong> <button type="button" class="copy-btn" data-copy="reference">Copia</button></dd></div>
                    <div><dt>Scadenza</dt><dd>{{ $order->payment_due_at->timezone('Europe/Rome')->format('d/m/Y H:i') }}</dd></div>
                </dl>
            </div>
            <div class="qr-card"><img src="{{ $qrDataUri }}" alt="QR SEPA con dati del bonifico"><strong>Scansiona nell'app della banca</strong><small>Verifica sempre i dati prima di autorizzare.</small></div>
        </section>
        <section class="booking-panel stack-mid">
            <div><h2>Ricevuta facoltativa</h2><p>Può anticipare il controllo in caso di urgenza, ma non attiva da sola i biglietti. PDF, JPEG o PNG, massimo 5 MB.</p></div>
            @if($order->transfer_receipt_uploaded_at)<div class="notice">Ricevuta ricevuta il {{ $order->transfer_receipt_uploaded_at->format('d/m/Y H:i') }}.</div>@endif
            <form action="{{ route('booking.receipt', $token) }}" method="POST" enctype="multipart/form-data" class="inline-form">
                @csrf<input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png" required><button class="btn">Carica ricevuta</button>
            </form>
        </section>
    @endif

    <section class="booking-panel"><h2>Partecipanti</h2><div class="participant-list">
        @foreach($order->tickets as $ticket)
            <article><div><strong>{{ $ticket->first_name }} {{ $ticket->last_name }}</strong><small>{{ $ticket->category?->name ?? $ticket->route_choice }} · € {{ number_format($ticket->price_paid, 2, ',', '.') }}</small></div>
                @if($order->status === 'paid' && $ticket->unique_ticket_code)<a class="btn btn-small" href="{{ route('ticket.download', $ticket->unique_ticket_code) }}">Scarica biglietto</a>@else<span class="ticket-pending">In attesa del pagamento</span>@endif
            </article>
        @endforeach
    </div></section>
</main>
<script>document.querySelectorAll('.copy-btn').forEach(button => button.addEventListener('click', async () => { const value = button.dataset.copyValue || document.getElementById(button.dataset.copy).textContent.trim(); await navigator.clipboard.writeText(value); button.textContent = 'Copiato'; setTimeout(() => button.textContent = 'Copia', 1400); }));</script>
</body>
</html>
