<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonifici · Admin 100 Miglia Monviso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-4 md:p-8 text-slate-800">
<main class="max-w-7xl mx-auto space-y-8">
    <header class="bg-white rounded-xl shadow-sm p-6 flex flex-wrap justify-between gap-4 items-center">
        <div><a href="{{ route('admin.dashboard') }}" class="text-blue-700 text-sm">← Pannello principale</a><h1 class="text-3xl font-bold">Bonifici intelligenti</h1><p class="text-slate-500">Riconciliazione per ordine, eccezioni e capienze.</p></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-red-600 font-semibold">Esci</button></form>
    </header>
    @if(session('success'))<div class="bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg p-4">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-100 text-red-800 border border-red-300 rounded-lg p-4"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <section class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold mb-4">Configurazione e disponibilità</h2>
        <form action="{{ route('admin.bank.settings') }}" method="POST" class="space-y-5">@csrf
            <label class="flex items-center gap-2 font-semibold"><input type="checkbox" name="bank_transfer_enabled" value="1" @checked(($settings['bank_transfer_enabled'] ?? '0') === '1')> Bonifico visibile nel checkout</label>
            <div class="grid md:grid-cols-3 gap-4">
                <label class="text-sm">Beneficiario<input class="border rounded p-2 w-full mt-1" name="bank_account_holder" value="{{ $settings['bank_account_holder'] ?? '' }}"></label>
                <label class="text-sm">IBAN<input class="border rounded p-2 w-full mt-1 font-mono" name="bank_iban" value="{{ $settings['bank_iban'] ?? '' }}"></label>
                <label class="text-sm">BIC<input class="border rounded p-2 w-full mt-1" name="bank_bic" value="{{ $settings['bank_bic'] ?? '' }}"></label>
                <label class="text-sm">Banca<input class="border rounded p-2 w-full mt-1" name="bank_name" value="{{ $settings['bank_name'] ?? '' }}"></label>
                <label class="text-sm">Data gara<input type="date" class="border rounded p-2 w-full mt-1" name="event_date" value="{{ substr($settings['event_date'] ?? '2027-05-16', 0, 10) }}" required></label>
                <label class="text-sm">Chiusura bonifico (giorni prima)<input type="number" class="border rounded p-2 w-full mt-1" name="bank_transfer_cutoff_days" value="{{ $settings['bank_transfer_cutoff_days'] ?? 7 }}" min="1" max="30" required></label>
                <label class="text-sm">Durata riserva (giorni)<input type="number" class="border rounded p-2 w-full mt-1" name="bank_transfer_reservation_days" value="{{ $settings['bank_transfer_reservation_days'] ?? 5 }}" min="1" max="10" required></label>
            </div>
            <div><h3 class="font-bold mb-2">Quote e capienze</h3><div class="grid md:grid-cols-3 gap-4">@foreach($categories as $category)
                <div class="border rounded-lg p-4"><strong>{{ $category->name }}</strong><label class="block text-sm mt-2">Prezzo €<input type="number" step="0.01" min="0" class="border rounded p-2 w-full" name="categories[{{ $category->id }}][price]" value="{{ $category->price }}" required></label><label class="block text-sm mt-2">Capienza <small>(vuoto = illimitata)</small><input type="number" min="1" class="border rounded p-2 w-full" name="categories[{{ $category->id }}][capacity]" value="{{ $category->capacity }}"></label></div>
            @endforeach</div></div>
            <label class="block text-sm max-w-xs">Add-on navetta €<input type="number" step="0.01" min="0" class="border rounded p-2 w-full mt-1" name="shuttle_addon_price" value="{{ $shuttleAddon?->price ?? 0 }}" required></label>
            <button class="bg-blue-700 text-white px-5 py-2 rounded-lg font-bold">Salva configurazione</button>
        </form>
    </section>

    <section class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-xl font-bold mb-4">Nuovo profilo CSV</h2><form action="{{ route('admin.bank.profiles.store') }}" method="POST" class="grid grid-cols-2 gap-3">@csrf
            <label class="text-sm col-span-2">Nome<input class="border rounded p-2 w-full" name="name" placeholder="Es. Intesa conto eventi" required></label>
            <label class="text-sm">Separatore<select class="border rounded p-2 w-full" name="delimiter"><option value=";">Punto e virgola</option><option value=",">Virgola</option></select></label>
            <label class="text-sm">Formato data<input class="border rounded p-2 w-full" name="date_format" value="d/m/Y" required></label>
            <label class="text-sm">Colonna data<input class="border rounded p-2 w-full" name="date_column" required></label><label class="text-sm">Colonna importo<input class="border rounded p-2 w-full" name="amount_column" required></label>
            <label class="text-sm col-span-2">Colonna causale/descrizione<input class="border rounded p-2 w-full" name="description_column" required></label>
            <label class="text-sm">Colonna ID movimento<input class="border rounded p-2 w-full" name="external_id_column"></label><label class="text-sm">Colonna valuta<input class="border rounded p-2 w-full" name="currency_column"></label>
            <button class="bg-slate-800 text-white px-4 py-2 rounded col-span-2">Salva profilo</button>
        </form></div>
        <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-xl font-bold mb-4">Importa estratto conto</h2><p class="text-sm text-slate-500 mb-4">Prima vedrai un'anteprima. La conferma automatica richiede causale univoca, importo esatto, EUR e movimento non duplicato.</p>
            @if($profiles->isEmpty())<div class="bg-amber-100 p-3 rounded">Crea prima un profilo di mappatura.</div>@else<form action="{{ route('admin.bank.import.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-4">@csrf
                <label class="block text-sm">Profilo<select name="profile_id" class="border rounded p-2 w-full" required>@foreach($profiles as $profile)<option value="{{ $profile->id }}">{{ $profile->name }}</option>@endforeach</select></label>
                <label class="block text-sm">File CSV<input type="file" name="csv_file" accept=".csv,.txt" class="border rounded p-2 w-full" required></label><button class="bg-blue-700 text-white px-4 py-2 rounded">Mostra anteprima</button>
            </form>@endif
        </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 overflow-x-auto">
        <div class="flex flex-wrap justify-between gap-3 mb-4"><h2 class="text-xl font-bold">Ordini con bonifico</h2><form method="GET"><select name="status" class="border rounded p-2" onchange="this.form.submit()"><option value="">Tutti gli stati</option>@foreach(['awaiting_transfer','transfer_review','paid','expired','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></form></div>
        <table class="min-w-full text-sm"><thead><tr class="border-b text-left text-slate-500"><th class="p-2">Ordine</th><th class="p-2">Referente</th><th class="p-2">Importo</th><th class="p-2">Stato / scadenza</th><th class="p-2">Riscontri</th><th class="p-2 min-w-72">Azioni</th></tr></thead><tbody>
        @forelse($orders as $order)<tr class="border-b align-top"><td class="p-2"><strong>{{ $order->booking_code }}</strong><div class="font-mono text-xs">{{ $order->bank_reference }}</div><small>{{ $order->tickets->count() }} partecipanti</small></td><td class="p-2">{{ $order->payment_contact_name }}<br><small>{{ $order->payment_contact_email }}</small></td><td class="p-2 font-bold">€ {{ number_format($order->total_amount, 2, ',', '.') }}</td><td class="p-2"><span class="font-semibold">{{ $order->status }}</span><br><small>{{ $order->payment_due_at?->format('d/m/Y H:i') }}</small>@if($order->transfer_review_reason)<p class="text-amber-700 mt-1">{{ $order->transfer_review_reason }}</p>@endif</td><td class="p-2">{{ $order->bankTransactions->count() }} movimenti<br>@if($order->transfer_receipt_path)<a class="text-blue-700" href="{{ route('admin.bank.receipt', $order) }}">Ricevuta privata</a>@else<small>Nessuna ricevuta</small>@endif @if($order->transferAudits->isNotEmpty())<details class="mt-2"><summary class="cursor-pointer text-blue-700">Storico ({{ $order->transferAudits->count() }})</summary><ul class="mt-1 space-y-1">@foreach($order->transferAudits->sortByDesc('created_at') as $audit)<li class="text-xs"><strong>{{ $audit->action }}</strong> · {{ $audit->created_at->format('d/m H:i') }}@if($audit->user) · {{ $audit->user->name }}@endif @if($audit->reason)<br>{{ $audit->reason }}@endif</li>@endforeach</ul></details>@endif</td><td class="p-2">
            @if($order->status !== 'paid' && $order->status !== 'cancelled')<div class="space-y-2"><form method="POST" action="{{ route('admin.bank.approve', $order) }}" class="flex gap-2">@csrf<input class="border rounded p-1 flex-1" name="reason" placeholder="Motivazione approvazione" required><button class="bg-emerald-700 text-white px-2 rounded">Approva</button></form><form method="POST" action="{{ route('admin.bank.reject', $order) }}" class="flex gap-2">@csrf<input class="border rounded p-1 flex-1" name="reason" placeholder="Motivazione rifiuto" required><button class="bg-amber-600 text-white px-2 rounded">Rifiuta</button></form><form method="POST" action="{{ route('admin.bank.cancel', $order) }}" class="flex gap-2">@csrf<input class="border rounded p-1 flex-1" name="reason" placeholder="Motivazione annullamento" required><button class="bg-red-700 text-white px-2 rounded">Annulla</button></form></div>@else<small>Nessuna azione necessaria</small>@endif
        </td></tr>@empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Nessun ordine trovato.</td></tr>@endforelse
        </tbody></table><div class="mt-4">{{ $orders->withQueryString()->links() }}</div>
    </section>
</main>
</body>
</html>
