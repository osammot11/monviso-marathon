@extends('layouts.app')

@section('content')
    <main>
        <section>
            <h1>Contatti</h1>
            <p class="top-margin-large">Per informazioni su iscrizioni, distanze e aspetti organizzativi puoi scrivere allo staff di Pescara Half Marathon.</p>
            <a href="mailto:info@pescarahalfmarathon.it" class="btn top-margin-xl mobile-fullwidth">Scrivici</a>
        </section>

        <section class="grid-2">
            <div class="card">
                <h2>Email</h2>
                <p class="top-margin-large"><a class="hyperlink" href="mailto:info@pescarahalfmarathon.it">info@pescarahalfmarathon.it</a></p>
            </div>
            <div class="card">
                <h2>Luogo evento</h2>
                <p class="top-margin-large">Pescara, Abruzzo · lungomare e Ponte del Mare.</p>
            </div>
        </section>
    </main>
@endsection
