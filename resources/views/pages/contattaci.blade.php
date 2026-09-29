@extends('layouts.app')

@section('content')
    <main>
        <section>
            <h1>Contatti</h1>
            <p class="top-margin-large">Per informazioni su gare, percorsi, ospitalità e aspetti organizzativi puoi contattare lo staff di 100 Miglia Monviso.</p>
            <a href="mailto:info@100migliamonviso.eu" class="btn top-margin-xl mobile-fullwidth">Scrivici</a>
        </section>

        <section class="grid-2">
            <div class="card">
                <h2>Email</h2>
                <p class="top-margin-large"><a class="hyperlink" href="mailto:info@100migliamonviso.eu">info@100migliamonviso.eu</a><br><a class="hyperlink" href="tel:+393498357091">+39 349 8357091</a></p>
            </div>
            <div class="card">
                <h2>Territorio</h2>
                <p class="top-margin-large">Saluzzo e le valli Po, Bronda e Varaita · Piemonte.</p>
            </div>
        </section>
    </main>
@endsection
