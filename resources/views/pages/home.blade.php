@extends('layouts.app')

@push('head')
    <script type="application/ld+json">
    {"@@context":"https://schema.org","@@type":"SportsEvent","name":"Pescara Half Marathon 2027","startDate":"2027-05-16T09:00:00+02:00","eventAttendanceMode":"https://schema.org/OfflineEventAttendanceMode","eventStatus":"https://schema.org/EventScheduled","url":"https://www.pescarahalfmarathon.it/","image":"https://www.pescarahalfmarathon.it/storage/site/logo/KVykCh4mU9tyOUrZue0gxA9pwzRWiNgum6P25SXr.png","description":"Mezza maratona competitiva, 10 km non competitiva e passeggiata sul lungomare di Pescara.","location":{"@@type":"Place","name":"Pescara","address":{"@@type":"PostalAddress","addressLocality":"Pescara","addressRegion":"Abruzzo","addressCountry":"IT"}},"organizer":{"@@type":"Organization","name":"ASD Run4Fun"}}
    </script>
@endpush

@section('content')
    <header class="phm-hero">
        <video class="phm-hero__video" autoplay muted loop playsinline preload="metadata" aria-hidden="true">
            <source src="/images/pescara/hero.mp4" type="video/mp4">
        </video>
        <div class="phm-hero__veil"></div>
        <div class="phm-hero__content">
            <p class="phm-eyebrow">Domenica 16 maggio 2027 · Pescara</p>
            <img class="phm-hero__logo" src="/images/pescara/logo.png" alt="Pescara Half Marathon 2027">
            <p class="phm-hero__copy">Un’occasione imperdibile per correre in uno scenario costiero unico, respirando l’aria del mare e sfidando il proprio cronometro su un tracciato veloce.</p>
            <div class="phm-countdown" id="countdown" aria-label="Conto alla rovescia per Pescara Half Marathon 2027">
                <div><strong id="days">000</strong><span>giorni</span></div><b>:</b>
                <div><strong id="hours">00</strong><span>ore</span></div><b>:</b>
                <div><strong id="minutes">00</strong><span>min</span></div><b>:</b>
                <div><strong id="seconds">00</strong><span>sec</span></div>
            </div>
            <div class="phm-actions">
                <a href="#percorso" class="phm-btn phm-btn--ghost">Scopri il percorso ↓</a>
                <a href="#quote" class="phm-btn phm-btn--light">Scegli la tua quota →</a>
            </div>
        </div>
    </header>

    <main class="phm-main">
        <section id="percorso" class="phm-section phm-course">
            <div class="phm-section-title"><span class="phm-icon">⚑</span><h2>Il Percorso</h2></div>
            <div class="phm-course__grid">
                <div>
                    <p>L’evento si snoda nel cuore di Pescara, lungo un suggestivo percorso pianeggiante e veloce che costeggia il Mare Adriatico e attraversa i luoghi simbolo della città, come il moderno Ponte del Mare.</p>
                    <p>La manifestazione prevede il percorso della Mezza Maratona (21,097 km), la 10 km non competitiva e una passeggiata di 5 km.</p>
                    <div class="phm-stats">
                        <div><strong>21,097</strong><span>km competitiva</span></div>
                        <div><strong>10</strong><span>km non competitiva</span></div>
                        <div><strong>5</strong><span>km passeggiata</span></div>
                    </div>
                </div>
                <div class="phm-course__visual" aria-label="Percorso sul lungomare di Pescara">
                    <div class="phm-route-line"></div><span class="phm-route-pin phm-route-pin--start">START</span><span class="phm-route-pin phm-route-pin--finish">FINISH</span>
                    <strong>Pescara<br><small>Mare Adriatico</small></strong>
                </div>
            </div>
        </section>

        <section id="quote" class="phm-section phm-pricing-section">
            <div class="phm-section-title"><span class="phm-icon">♢</span><h2>Scegli la tua quota</h2></div>
            <p class="phm-section-lead">Seleziona la categoria più adatta a te e completa l’iscrizione in pochi minuti.</p>
            <div class="phm-pricing-grid">
                <article class="phm-price-card phm-price-card--featured">
                    <div class="phm-card-topline"><span>Competitiva</span><span class="phm-status">Iscrizioni aperte</span></div>
                    <h3>21KM Competitiva</h3><p>La gara principale. Tessera FIDAL o Runcard e certificato medico agonistico richiesti.</p>
                    <div class="phm-price"><span>€</span><strong>30</strong><small>,00 / persona</small></div>
                    <ul><li>✓ Percorso omologato 21,097 km</li><li>✓ Pacco gara e medaglia</li><li>✓ Pettorale con chip</li></ul>
                    <a href="/iscrizione?percorso=21KM%20Competitiva" class="phm-btn phm-btn--blue">Iscriviti ora</a>
                </article>
                <article class="phm-price-card">
                    <div class="phm-card-topline"><span>Non competitiva</span><span class="phm-status">Promo</span></div>
                    <h3>10KM non Competitiva</h3><p>Dieci chilometri per vivere il lungomare di Pescara al proprio ritmo.</p>
                    <div class="phm-price"><span>€</span><strong>10</strong><small>,00 / persona</small></div>
                    <ul><li>✓ Aperta a runner e appassionati</li><li>✓ Pacco gara incluso</li><li>✓ Assistenza sul percorso</li></ul>
                    <a href="/iscrizione?percorso=10KM%20non%20Competitiva" class="phm-btn phm-btn--blue">Iscriviti ora</a>
                </article>
                <article class="phm-price-card">
                    <div class="phm-card-topline"><span>Passeggiata</span><span class="phm-status">Per tutti</span></div>
                    <h3>5KM Passeggiata</h3><p>Una passeggiata sul percorso dell’evento, aperta a famiglie e accompagnatori.</p>
                    <div class="phm-price"><span>€</span><strong>5</strong><small>,00 / persona</small></div>
                    <ul><li>✓ Percorso accessibile</li><li>✓ Atmosfera dell’evento</li><li>✓ Nessuna tessera richiesta</li></ul>
                    <a href="/iscrizione?percorso=5KM%20Passeggiata" class="phm-btn phm-btn--blue">Iscriviti ora</a>
                </article>
            </div>
        </section>

        <section class="phm-section phm-action-grid">
            <article class="phm-action-card phm-action-card--navy"><span>Per gruppi e società</span><h3>Iscrivi la tua società sportiva</h3><p>Inserisci più atleti e completa un unico pagamento cumulativo per tutta la squadra.</p><a href="/iscrizione" class="phm-text-link">Crea iscrizione societaria →</a></article>
            <article class="phm-action-card phm-action-card--blue"><span>Per iscritti</span><h3>Hai già una prenotazione?</h3><p>Contatta lo staff per documenti, ricevute, pettorali e assistenza sulla tua iscrizione.</p><a href="/contattaci" class="phm-text-link">Gestisci la tua iscrizione →</a></article>
        </section>

        <section class="phm-section phm-how">
            <div class="phm-section-title"><span class="phm-icon">✓</span><h2>Come funziona</h2></div>
            <div class="phm-steps">
                <article><b>1</b><h3>Scegli la quota</h3><p>Seleziona la distanza e compila i dati dell’atleta.</p></article>
                <article><b>2</b><h3>Completa l’iscrizione</h3><p>Aggiungi i partecipanti e scegli il metodo di pagamento.</p></article>
                <article><b>3</b><h3>Ricevi la conferma</h3><p>Troverai il riepilogo e il biglietto direttamente via email.</p></article>
                <article><b>4</b><h3>Preparati a correre</h3><p>Porta con te la conferma il giorno dell’evento.</p></article>
            </div>
        </section>

        <section id="sponsor" class="phm-section phm-sponsors">
            <div class="phm-section-title"><span class="phm-icon">♢</span><h2>I Nostri Sponsor</h2></div>
            <p class="phm-section-lead">Ringraziamo le aziende e le istituzioni che rendono possibile Pescara Half Marathon 2027.</p>
            <div class="phm-sponsor-grid">
                <img src="/images/pescara/sponsor-pienne.jpg" alt="Pienne" loading="lazy"><img src="/images/pescara/sponsor-di-francesco.png" alt="Di Francesco Auto" loading="lazy"><img src="/images/pescara/sponsor-di-vincenzo.png" alt="Di Vincenzo" loading="lazy"><img src="/images/pescara/sponsor-massigen.png" alt="Massigen" loading="lazy"><img src="/images/pescara/sponsor-oropuro.png" alt="Oropuro" loading="lazy"><img src="/images/pescara/sponsor-sisofo.png" alt="Sisofo" loading="lazy">
            </div>
        </section>
    </main>
@endsection

@push('scripts')<script src="/homepage-countdown.js?v=3.0"></script>@endpush
