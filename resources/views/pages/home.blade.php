@extends('layouts.app')

@push('head')
    <script type="application/ld+json">
    {"@@context":"https://schema.org","@@type":"SportsEvent","name":"100 Miglia Monviso 2026","startDate":"2026-07-17T21:00:00+02:00","endDate":"2026-07-19T23:59:00+02:00","eventAttendanceMode":"https://schema.org/OfflineEventAttendanceMode","eventStatus":"https://schema.org/EventScheduled","url":"https://www.100migliamonviso.eu/","image":"https://www.100migliamonviso.eu/wp-content/uploads/2024/12/100MM-24_PH-Stefano-Jeantet-52-scaled.jpg","description":"Quattro gare di trail running tra Valle Po, Valle Bronda e Valle Varaita, ai piedi del Monviso.","location":{"@@type":"Place","name":"Terres Monviso","address":{"@@type":"PostalAddress","addressRegion":"Piemonte","addressCountry":"IT"}},"organizer":{"@@type":"Organization","name":"100 Miglia Monviso"}}
    </script>
@endpush

@section('content')
    <header class="mm-hero">
        <div class="mm-hero__media" aria-hidden="true"></div>
        <div class="mm-hero__shade" aria-hidden="true"></div>
        <div class="mm-shell mm-hero__content">
            <p class="mm-kicker">Terres Monviso · Piemonte</p>
            <h1>Due versanti.<br><em>Una sfida.</em></h1>
            <p class="mm-hero__lead">Un viaggio oltre il limite, lungo sentieri selvaggi e borghi alpini, al cospetto del Re di Pietra.</p>
            <div class="mm-hero__actions">
                <a href="#gare" class="mm-button mm-button--solid">Scopri le gare <span>↘</span></a>
                <a href="https://www.100migliamonviso.eu/programma/" class="mm-button mm-button--line" target="_blank" rel="noopener">Programma 2026</a>
            </div>
        </div>
        <div class="mm-hero__date"><span>17—19</span><strong>Luglio 2026</strong></div>
        <a class="mm-scroll" href="#gare" aria-label="Vai alle gare"><span></span>Scorri per esplorare</a>
    </header>

    <main>
        <section id="gare" class="mm-races mm-section">
            <div class="mm-shell">
                <div class="mm-heading">
                    <div><p class="mm-kicker mm-kicker--dark">Scegli la tua sfida</p><h2>Quattro gare.<br>Un solo orizzonte.</h2></div>
                    <p>Dall’ultra trail che unisce le valli alla corsa verticale ai piedi del Monviso: trova la distanza che ti somiglia.</p>
                </div>
                <div class="mm-race-grid">
                    <a class="mm-race mm-race--featured" href="/iscrizione?percorso=M160K">
                        <span class="mm-race__number">01</span><div class="mm-race__tag">La regina</div>
                        <div class="mm-race__body"><p>Saluzzo → Saluzzo</p><h3>M160K</h3><div class="mm-race__stats"><strong>160 <small>km</small></strong><strong>9.000 <small>D+</small></strong></div></div>
                        <div class="mm-race__foot"><span>17 luglio · ore 21:00</span><b>↗</b></div>
                    </a>
                    <a class="mm-race" href="/iscrizione?percorso=M50K">
                        <span class="mm-race__number">02</span><div class="mm-race__body"><p>Melle → Saluzzo</p><h3>M50K</h3><div class="mm-race__stats"><strong>50 <small>km</small></strong><strong>3.000 <small>D+</small></strong></div></div>
                        <div class="mm-race__foot"><span>18 luglio · ore 15:00</span><b>↗</b></div>
                    </a>
                    <a class="mm-race" href="/iscrizione?percorso=M20K">
                        <span class="mm-race__number">03</span><div class="mm-race__body"><p>Verzuolo → Saluzzo</p><h3>M20K</h3><div class="mm-race__stats"><strong>20 <small>km</small></strong><strong>1.000 <small>D+</small></strong></div></div>
                        <div class="mm-race__foot"><span>19 luglio 2026</span><b>↗</b></div>
                    </a>
                    <a class="mm-race mm-race--image" href="/iscrizione?percorso=MTRAIL">
                        <span class="mm-race__number">04</span><div class="mm-race__body"><p>Crissolo</p><h3>MTRAIL</h3><div class="mm-race__stats"><strong>26 <small>km</small></strong><strong>1.900 <small>D+</small></strong></div></div>
                        <div class="mm-race__foot"><span>30 agosto 2026</span><b>↗</b></div>
                    </a>
                </div>
            </div>
        </section>

        <section class="mm-manifesto mm-section">
            <div class="mm-shell mm-manifesto__grid">
                <div class="mm-manifesto__image"><span>44.67° N<br>7.09° E</span></div>
                <div class="mm-manifesto__copy">
                    <p class="mm-kicker mm-kicker--dark">La sfida di Terres Monviso</p>
                    <h2>Qui la montagna non fa da sfondo. <em>È protagonista.</em></h2>
                    <p>Due vallate alpine, atleti da tutto il mondo, rifugi e comunità uniti in un grande weekend di sport. La 100 Miglia Monviso è un’esperienza collettiva che attraversa la Valle Po, la Valle Bronda e la Valle Varaita.</p>
                    <div class="mm-facts"><div><strong>3</strong><span>vallate</span></div><div><strong>42h</strong><span>tempo limite</span></div><div><strong>20+</strong><span>comuni coinvolti</span></div></div>
                    <a class="mm-arrow-link" href="https://www.100migliamonviso.eu/programma/" target="_blank" rel="noopener">Vivi l’esperienza <span>→</span></a>
                </div>
            </div>
        </section>

        <section id="gallery" class="mm-gallery mm-section">
            <div class="mm-shell">
                <div class="mm-heading mm-heading--light"><div><p class="mm-kicker">Dentro la sfida</p><h2>Polvere. Roccia.<br>Respiro.</h2></div><a class="mm-arrow-link" href="https://www.100migliamonviso.eu/gallery/" target="_blank" rel="noopener">Esplora la gallery <span>→</span></a></div>
                <div class="mm-gallery__grid">
                    <figure class="mm-gallery__wide"><img src="/images/monviso/runner.jpg" alt="Trail runner sui sentieri del Monviso" loading="lazy"><figcaption><span>Valle Varaita</span><b>Il sentiero chiama</b></figcaption></figure>
                    <figure><img src="/images/monviso/gallery-1.jpg" alt="Atleta in gara sui sentieri alpini" loading="lazy"></figure>
                    <figure><img src="/images/monviso/gallery-2.jpg" alt="Passaggio di un atleta durante la 100 Miglia Monviso" loading="lazy"></figure>
                    <figure><img src="/images/monviso/gallery-3.jpg" alt="Panorama di gara nelle Terres Monviso" loading="lazy"></figure>
                </div>
            </div>
        </section>

        <section class="mm-values mm-section">
            <div class="mm-shell"><p class="mm-kicker mm-kicker--dark">Più di una gara</p><div class="mm-values__grid">
                <h2>Lascia solo<br>le tue impronte.</h2>
                <div><span class="mm-value-icon">◎</span><h3>Ambiente</h3><p>Aderiamo alla campagna “Io non getto i miei rifiuti” e promuoviamo un modo responsabile di vivere il territorio.</p></div>
                <div><span class="mm-value-icon">△</span><h3>Territorio</h3><p>Una rete di borghi, rifugi, associazioni e volontari rende possibile ogni chilometro della sfida.</p></div>
            </div></div>
        </section>

        <section class="mm-cta">
            <div class="mm-cta__bg" aria-hidden="true"></div><div class="mm-cta__veil" aria-hidden="true"></div>
            <div class="mm-shell mm-cta__content"><p class="mm-kicker">La sfida è aperta</p><h2>Il Monviso<br>ti aspetta.</h2><p>Scegli la tua distanza e preparati a vivere la montagna da una prospettiva nuova.</p><a class="mm-button mm-button--solid" href="/iscrizione">Iscriviti ora <span>↗</span></a></div>
        </section>
    </main>
@endsection
