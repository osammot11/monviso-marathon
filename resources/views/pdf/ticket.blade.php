<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Biglietto {{ $ticket->unique_ticket_code }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #191d2f; }
        .ticket-container { padding: 20px; margin: 20px auto; max-width: 600px; text-align: center; }
        .header { font-size: 32px; font-weight: bold; text-transform: uppercase; }
        .details { margin: 20px 0; text-align: left; line-height: 1.6; font-size: 18px; }
        .ticket-code { box-sizing: border-box; width: 100%; font-size: 24px; padding: 40px 0px; border: 1px solid #25386a; display: inline-block; margin-top: 15px; background-color: #d8ad4a }
        .small-p {font-size: 13px; color: #626569; line-height: 1.6;}
        .center-text {text-align: center;}
   </style>
</head>
<body>
    <div class="ticket-container">
        <div class="header">
            100 MIGLIA MONVISO 2026
        </div>
        <p class="center-text">17—19 LUGLIO 2026 · TERRES MONVISO</p>
        <div class="details">
            <p><strong>Partecipante:</strong> {{ $ticket->first_name }} {{ $ticket->last_name }}</p>
            <p><strong>Percorso:</strong> {{ $ticket->route_choice }}</p>
            <p><strong>Taglia Maglia:</strong> {{ $ticket->tshirt_size }}</p>
            <p><strong>Celiachia:</strong> {{ $ticket->celiac }}</p>
            <p><strong>Navetta:</strong> {{ $ticket->shuttle_needed }}</p>
            <p class="small-p">Legenda per i campi "Celiachia" e "Navetta":<br>0 = NO<br>1 = SÌ</p>
            <p><strong>Importo pagato:</strong> {{ $ticket->price_paid }}€</p>
            <p class="small-p">Il documento funge da ricevuta dell’importo sopra indicato. Per richieste amministrative scrivi a info@100migliamonviso.eu indicando il codice pettorale.</p>
        </div>
        <div class="ticket-code">
            <p>CODICE PETTORALE</p>
            <p style="font-weight: bold;">{{ $ticket->unique_ticket_code }}</p>
        </div>
        <p class="small-p center-text">Il pettorale è nominativo. Porta con te questo documento, in formato digitale o cartaceo, al check-in della gara.</p>
    </div>
</body>
</html>
