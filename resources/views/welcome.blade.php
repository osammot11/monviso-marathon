<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iscrizione Pescara Half Marathon 2027</title>
    <link rel="stylesheet" href="{{ asset('css/style.css?v=1.8') }}">
</head>
<body class="center-items checkout-body">

    <div class="checkout-wrapper stack-large">
        <a href="/" class="checkout-logo"><img src="/images/pescara/logo.png" alt="Pescara Half Marathon 2027"></a>
        <h1 class="center-text">Iscrizione Pescara Half Marathon 2027</h1>
        <p class="center-text checkout-intro">16 maggio 2027 · scegli la distanza e registra i partecipanti.</p>

        <form action="{{ route('checkout.store') }}" method="POST" id="marathon-form">
            @if($errors->any())
                <div class="bg-primary-hover">
                    <strong>Attenzione!</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @csrf

            <div class="box-associazione stack-mid">
                <label>
                    <input type="checkbox" name="is_sports_association" value="1" onchange="document.getElementById('assoc_name_div').style.display = this.checked ? 'block' : 'none'">
                    <span>Siamo un'associazione sportiva</span>
                </label>
                <div class="label-box" id="assoc_name_div" style="display:none">
                    <label>Nome Associazione</label>
                    <input type="text" name="association_name">
                </div>
            </div>

            <div id="tickets-container">
                <div class="ticket-block top-margin-large">
                    <h3>Partecipante #1</h3>

                    <div class="grid-2 gap-20 top-margin-mid">
                        <div class="label-box">
                            <label>Nome</label>
                            <input type="text" name="tickets[0][first_name]" placeholder="Nome" required>
                        </div>

                        <div class="label-box">
                            <label>Cognome</label>
                            <input type="text" name="tickets[0][last_name]" placeholder="Cognome" required>
                        </div>

                        <div class="label-box">
                            <label>Scelta del percorso</label>
                            <select name="tickets[0][route_choice]" required>
                                <option value="">Scegli la distanza</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->name }}" {{ request('percorso') == $category->name ? 'selected' : '' }}>
                                        {{ $category->name }} · € {{ number_format($category->current_price, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="label-box">
                            <label>Data di nascita</label>
                            <input type="date" name="tickets[0][dob]" required title="Data di nascita">
                        </div>

                        <div class="label-box">
                            <label>Città di nascita</label>
                            <input type="text" name="tickets[0][birth_place]" placeholder="Luogo di nascita" required>
                        </div>

                        <div class="label-box">
                            <label>Nazionalità (sigla)</label>
                            <input type="text" name="tickets[0][nationality]" placeholder="Nazionalità (es. IT)" maxlength="2" oninput="this.value = this.value.toUpperCase()" required>
                        </div>

                        <div class="label-box">
                            <label>Codice fiscale (se cittadino italiano)</label>
                            <input type="text" name="tickets[0][codice_fiscale]" placeholder="Codice Fiscale (Obbligatorio se IT)" oninput="this.value = this.value.toUpperCase()">
                        </div>

                        <div class="label-box">
                            <label>Indirizzo mail</label>
                            <input type="email" name="tickets[0][email]" placeholder="Email" required>
                        </div>

                        <div class="label-box">
                            <label>Telefono</label>
                            <input type="text" name="tickets[0][phone]" placeholder="Telefono" required>
                        </div>

                        <div class="label-box">
                            <label>Taglia maglia</label>
                            <select name="tickets[0][tshirt_size]" required>
                                <option value="">Taglia Maglia</option>
                                <option value="XS">XS</option>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid-3 gap-20 top-margin-large">
                        <div class="label-box">
                            <label>Indirizzo</label>
                            <input type="text" name="tickets[0][residence_address]" placeholder="Indirizzo" required>
                        </div>

                        <div class="label-box">
                            <label>Città</label>
                            <input type="text" name="tickets[0][city]" placeholder="Città" required>
                        </div>

                        <div class="label-box">
                            <label>CAP</label>
                            <input type="text" name="tickets[0][zip_code]" placeholder="CAP" required>
                        </div>

                        <div class="label-box">
                            <label>Paese</label>
                            <select class="country-select" onchange="toggleCountry(this)">
                                <option value="Italia" selected>Italia</option>
                                <option value="Estero">Estero (Specifica...)</option>
                            </select>

                            <input type="hidden" name="tickets[0][country]" class="country-hidden" value="Italia">

                            <input type="text" placeholder="Specifica nazione" class="country-text hidden" oninput="updateCountryHidden(this)">
                        </div>

                        <div class="label-box">
                            <label>Regione</label>
                            <select name="tickets[0][region]" class="region-select" onchange="updateProvinces(this)" required></select>

                            <input type="text" name="tickets[0][region]" placeholder="Regione/Stato" class="region-text hidden" disabled required>
                        </div>

                        <div class="label-box">
                            <label>Provincia</label>
                            <select name="tickets[0][province]" class="province-select" required></select>

                            <input type="text" name="tickets[0][province]" placeholder="Provincia/Contea" class="province-text hidden" disabled required>
                        </div>
                    </div>

                    <div class="top-margin-large">
                        <label>
                            <input type="hidden" name="tickets[0][shuttle_needed]" value="0">
                            <input type="checkbox" name="tickets[0][shuttle_needed]" value="1"> Navetta?
                        </label>

                        <label>
                            <input type="hidden" name="tickets[0][celiac]" value="0">
                            <input type="checkbox" name="tickets[0][celiac]" value="1"> Celiaco?
                        </label>
                    </div>
                </div>
            </div>

            <button class="top-margin-large grey-btn" type="button" onclick="addParticipant()">+ Aggiungi un altro partecipante</button>

            <input type="hidden" name="is_mutua_member" value="0">

            <div class="stack-mid">
              <div class="label-box top-margin-xl">
                  <label>Metodo di Pagamento</label>
                  <select name="payment_method" id="payment-method" required>
                      <option value="stripe">Carta</option>
                      <option value="paypal">PayPal</option>
                      @if($bankTransferAvailable)
                          <option value="bank_transfer">Bonifico bancario guidato</option>
                      @endif
                  </select>
              </div>

              @if($bankTransferAvailable)
                  <div id="bank-contact" class="grid-2 gap-20" hidden>
                      <div class="label-box">
                          <label>Referente della prenotazione</label>
                          <input type="text" name="payment_contact_name" maxlength="255" autocomplete="name">
                      </div>
                      <div class="label-box">
                          <label>Email del referente</label>
                          <input type="email" name="payment_contact_email" maxlength="255" autocomplete="email">
                      </div>
                      <p class="grid-span-all">Riceverai istruzioni, promemoria e un link sicuro per gestire l'intero ordine. Il posto resta riservato per cinque giorni.</p>
                  </div>
              @endif
            
              <label>
                  <input type="checkbox" value="1" checked required>Procedendo al pagamento dichiaro di accettare la <a href="/privacy-policy">privacy policy</a> e il <a href="/regolamento">regolamento</a> dell'evento
              </label>

              <label>
                  <input type="checkbox" value="1" checked required>Acconsento al trattamento dei miei dati particolari relativi alla salute (es. celiachia), forniti volontariamente, al fine di gestire correttamente le esigenze alimentari durante l’evento, ai sensi dell’art. 9 del Regolamento UE 2016/679 (GDPR).
              </label>

              <button class="btn fullwidth" id="payment-button" type="submit">Procedi al Pagamento - {{ $currentPrice }}€</button>
            </div>
        </form>
    </div>

    <script>
        // --- 1. DATI GEOGRAFICI ---
        const italyLocations = {
            "Abruzzo": ["Chieti", "L'Aquila", "Pescara", "Teramo"],
            "Basilicata": ["Matera", "Potenza"],
            "Calabria": ["Catanzaro", "Cosenza", "Crotone", "Reggio Calabria", "Vibo Valentia"],
            "Campania": ["Avellino", "Benevento", "Caserta", "Napoli", "Salerno"],
            "Emilia-Romagna": ["Bologna", "Ferrara", "Forlì-Cesena", "Modena", "Parma", "Piacenza", "Ravenna", "Reggio Emilia", "Rimini"],
            "Friuli-Venezia Giulia": ["Gorizia", "Pordenone", "Trieste", "Udine"],
            "Lazio": ["Frosinone", "Latina", "Rieti", "Roma", "Viterbo"],
            "Liguria": ["Genova", "Imperia", "La Spezia", "Savona"],
            "Lombardia": ["Bergamo", "Brescia", "Como", "Cremona", "Lecco", "Lodi", "Mantova", "Milano", "Monza e della Brianza", "Pavia", "Sondrio", "Varese"],
            "Marche": ["Ancona", "Ascoli Piceno", "Fermo", "Macerata", "Pesaro e Urbino"],
            "Molise": ["Campobasso", "Isernia"],
            "Piemonte": ["Alessandria", "Asti", "Biella", "Cuneo", "Novara", "Torino", "Verbano-Cusio-Ossola", "Vercelli"],
            "Puglia": ["Bari", "Barletta-Andria-Trani", "Brindisi", "Foggia", "Lecce", "Taranto"],
            "Sardegna": ["Cagliari", "Nuoro", "Oristano", "Sassari", "Sud Sardegna"],
            "Sicilia": ["Agrigento", "Caltanissetta", "Catania", "Enna", "Messina", "Palermo", "Ragusa", "Siracusa", "Trapani"],
            "Toscana": ["Arezzo", "Firenze", "Grosseto", "Livorno", "Lucca", "Massa-Carrara", "Pisa", "Pistoia", "Prato", "Siena"],
            "Trentino-Alto Adige": ["Bolzano", "Trento"],
            "Umbria": ["Perugia", "Terni"],
            "Valle d'Aosta": ["Aosta"],
            "Veneto": ["Belluno", "Padova", "Rovigo", "Treviso", "Venezia", "Verona", "Vicenza"]
        };

        function populateRegions(block) {
            const regionSelect = block.querySelector('.region-select');
            regionSelect.innerHTML = '<option value="">Seleziona Regione</option>';
            Object.keys(italyLocations).sort().forEach(region => {
                const isSelected = region === 'Abruzzo' ? 'selected' : '';
                regionSelect.innerHTML += `<option value="${region}" ${isSelected}>${region}</option>`;
            });
            updateProvinces(regionSelect, true);
        }

        function updateProvinces(regionSelect, setPescaraDefault = false) {
            const block = regionSelect.closest('.ticket-block');
            const provinceSelect = block.querySelector('.province-select');
            const region = regionSelect.value;

            provinceSelect.innerHTML = '<option value="">Seleziona Provincia</option>';
            if (italyLocations[region]) {
                italyLocations[region].sort().forEach(prov => {
                    const isSelected = (setPescaraDefault && prov === 'Pescara') ? 'selected' : '';
                    provinceSelect.innerHTML += `<option value="${prov}" ${isSelected}>${prov}</option>`;
                });
            }
        }

        function toggleCountry(countrySelect) {
            const block = countrySelect.closest('.ticket-block');
            const isItaly = countrySelect.value === 'Italia';
            const countryHidden = block.querySelector('.country-hidden');
            const countryText = block.querySelector('.country-text');
            const regionSelect = block.querySelector('.region-select');
            const regionText = block.querySelector('.region-text');
            const provinceSelect = block.querySelector('.province-select');
            const provinceText = block.querySelector('.province-text');

            if (isItaly) {
                countryHidden.value = 'Italia';
                countryText.classList.add('hidden');
                countryText.required = false;
                regionText.classList.add('hidden');
                regionText.disabled = true;
                regionSelect.classList.remove('hidden');
                regionSelect.disabled = false;
                provinceText.classList.add('hidden');
                provinceText.disabled = true;
                provinceSelect.classList.remove('hidden');
                provinceSelect.disabled = false;
            } else {
                countryHidden.value = countryText.value;
                countryText.classList.remove('hidden');
                countryText.required = true;
                regionSelect.classList.add('hidden');
                regionSelect.disabled = true;
                regionText.classList.remove('hidden');
                regionText.disabled = false;
                provinceSelect.classList.add('hidden');
                provinceSelect.disabled = true;
                provinceText.classList.remove('hidden');
                provinceText.disabled = false;
            }
        }

        function updateCountryHidden(textInput) {
            const block = textInput.closest('.ticket-block');
            block.querySelector('.country-hidden').value = textInput.value;
        }

        function updateTotal() {
            const ticketBlocks = document.querySelectorAll('.ticket-block');
            const prices = @json($categories->pluck('current_price', 'name')->map(fn ($price) => (float) $price));
            let total = 0;
            ticketBlocks.forEach((block, index) => {
                const route = block.querySelector('[name*="[route_choice]"]').value;
                if ((index + 1) % 11 !== 0) total += prices[route] ?? 0;
                if (block.querySelector('input[name*="[shuttle_needed]"][type="checkbox"]')?.checked) {
                    total += Number(@json($shuttleAddonPrice));
                }
            });

            const btn = document.getElementById('payment-button');
            btn.innerText = `Procedi al Pagamento - ${total}€`;
        }

        let participantCount = 0;

        function addParticipant() {
            const container = document.getElementById('tickets-container');
            const clone = container.querySelector('.ticket-block').cloneNode(true);

            participantCount++;

            clone.querySelectorAll('input, select').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/\[\d+\]/, '[' + participantCount + ']');
                }

                if (input.type !== 'hidden' && input.type !== 'checkbox') {
                    input.value = "";
                }

                if (input.type === 'checkbox') {
                    input.checked = false;
                }
            });

            clone.querySelector('.country-select').value = "Italia";
            clone.querySelector('.country-hidden').value = "Italia";
            toggleCountry(clone.querySelector('.country-select'));
            populateRegions(clone);

            container.appendChild(clone);

            reindexParticipants();
            updateTotal();
        }

        function removeParticipant(button) {
            button.closest('.ticket-block').remove();

            reindexParticipants();
            updateTotal();
        }

        function reindexParticipants() {
            const blocks = document.querySelectorAll('.ticket-block');

            blocks.forEach((block, index) => {
                const h3 = block.querySelector('h3');

                if (index === 0) {
                    h3.innerHTML = "Partecipante #1";
                } else {
                    h3.innerHTML = `Partecipante #${index + 1} <button class="remove-btn" type="button" onclick="removeParticipant(this)">Rimuovi</button>`;
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const firstBlock = document.querySelector('.ticket-block');
            populateRegions(firstBlock);
            updateTotal();
            document.getElementById('tickets-container').addEventListener('change', function(event) {
                if (event.target.name && (event.target.name.includes('[route_choice]') || event.target.name.includes('[shuttle_needed]'))) updateTotal();
            });

            const paymentMethod = document.getElementById('payment-method');
            const bankContact = document.getElementById('bank-contact');
            if (bankContact) {
                const toggleBankContact = () => {
                    const enabled = paymentMethod.value === 'bank_transfer';
                    bankContact.hidden = !enabled;
                    bankContact.querySelectorAll('input').forEach(input => input.required = enabled);
                };
                paymentMethod.addEventListener('change', toggleBankContact);
                toggleBankContact();
            }
        });
    </script>
</body>
</html>
