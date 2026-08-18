<?php

namespace App\Http\Controllers;

use App\Jobs\SendBankTransferInstructions;
use App\Models\Order;
use App\Models\Ticket;
use App\Services\BankTransferService;
use App\Services\CategoryCapacityService;
use App\Services\OrderPaymentFinalizer;
use App\Services\OrderPricingService;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class CheckoutController extends Controller
{
    public function store(Request $request, BankTransferService $bankTransfer, CategoryCapacityService $capacity, OrderPricingService $pricing)
    {
        // 1. Validazione massiccia
        $validatedData = $request->validate([
            'payment_method' => 'required|in:stripe,paypal,bank_transfer',
            'payment_contact_name' => 'required_if:payment_method,bank_transfer|nullable|string|max:255',
            'payment_contact_email' => 'required_if:payment_method,bank_transfer|nullable|email|max:255',
            'is_sports_association' => 'boolean',
            'association_name' => 'required_if:is_sports_association,1|nullable|string|max:255',
            'is_mutua_member' => 'boolean', 
            'tickets' => 'required|array|min:1',
            'tickets.*.first_name' => 'required|string|max:255',
            'tickets.*.last_name' => 'required|string|max:255',
            'tickets.*.codice_fiscale' => 'nullable|string|max:16', 
            'tickets.*.route_choice' => 'required|in:21KM Competitiva,10KM non Competitiva,5KM Passeggiata',
            'tickets.*.dob' => 'required|date',
            'tickets.*.birth_place' => 'required|string|max:255',
            'tickets.*.residence_address' => 'required|string|max:255',
            'tickets.*.city' => 'required|string|max:255',
            'tickets.*.zip_code' => 'required|string|max:20',
            'tickets.*.province' => 'required|string|max:255',
            'tickets.*.region' => 'required|string|max:255',
            'tickets.*.country' => 'required|string|max:255',
            'tickets.*.nationality' => 'required|string|max:2',
            'tickets.*.email' => 'required|email',
            'tickets.*.phone' => 'required|string|max:20',
            'tickets.*.tshirt_size' => 'required|in:XS,S,M,L,XL,XXL',
            'tickets.*.shuttle_needed' => 'required|boolean',
            'tickets.*.celiac' => 'required|boolean',
        ]);

        $appliedDiscountLabel = null;

        // --- LOGICA PROMO GRUPPI (Ogni 11 biglietti, 1 è gratis) ---
        $totalTickets = count($validatedData['tickets']);
        $freeTicketsCount = floor($totalTickets / 11);
        if ($validatedData['payment_method'] === 'stripe' && (blank(config('services.stripe.secret')) || blank(config('services.stripe.key')))) {
            return back()
                ->withInput()
                ->withErrors(['payment_method' => 'Stripe non è configurato: controlla STRIPE_SECRET e STRIPE_KEY nel file .env oppure la configurazione Laravel in cache.']);
        }

        if ($validatedData['payment_method'] === 'bank_transfer' && ! $bankTransfer->isAvailable()) {
            return back()->withInput()->withErrors(['payment_method' => 'Il bonifico non è disponibile o non è ancora configurato.']);
        }

        // Aggiungiamo l'etichetta se è scattata la promo
        if ($freeTicketsCount > 0) {
            $promoLabel = "PROMO_11_GRATIS";
            $appliedDiscountLabel = $appliedDiscountLabel ? $appliedDiscountLabel . ' + ' . $promoLabel : $promoLabel;
        }

        // 3. Creazione Ordine e Biglietti
        $creation = DB::transaction(function () use ($request, $validatedData, $appliedDiscountLabel, $bankTransfer, $capacity, $pricing) {
            $categories = $capacity->lockAndResolve($validatedData['tickets']);
            $totalAmount = collect($validatedData['tickets'])
                ->values()
                ->map(function (array $ticket, int $index) use ($categories, $pricing) {
                    $base = (($index + 1) % 11 === 0) ? 0 : $pricing->categoryPrice($categories[$ticket['route_choice']]);
                    return $base + $pricing->addonAmount($ticket);
                })
                ->sum();
            $bankPrepared = $validatedData['payment_method'] === 'bank_transfer'
                ? $bankTransfer->prepareOrderAttributes($validatedData['payment_contact_name'], $validatedData['payment_contact_email'])
                : ['attributes' => [], 'public_token' => null];

            $order = Order::create(array_merge([
                'group_code' => 'PHM_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'total_amount' => $totalAmount,
                'payment_method' => $validatedData['payment_method'],
                'is_sports_association' => $request->has('is_sports_association'),
                'association_name' => $validatedData['association_name'] ?? null,
                'discount_code' => $appliedDiscountLabel,
                'status' => 'pending',
            ], $bankPrepared['attributes']));

            foreach ($validatedData['tickets'] as $index => $data) {
                // L'undicesimo biglietto (indice 10, 21, 32...) ha prezzo 0€
                $isFreeTicket = (($index + 1) % 11 == 0);
                $category = $categories[$data['route_choice']];
                $basePrice = $isFreeTicket ? 0 : $pricing->categoryPrice($category);
                $addonAmount = $pricing->addonAmount($data);
                $ticketPrice = $basePrice + $addonAmount;

                $order->tickets()->create(array_merge($data, [
                    'event_category_id' => $category->id,
                    'unique_ticket_code' => null,
                    'price_paid' => $ticketPrice,
                    'base_price' => $basePrice,
                    'addon_amount' => $addonAmount,
                    'payment_tag' => ucfirst($validatedData['payment_method']),
                    'status' => 'pending',
                ]));
            }

            return ['order' => $order, 'public_token' => $bankPrepared['public_token']];
        });
        $order = $creation['order'];
        $totalAmount = (float) $order->total_amount;

        // 5. Redirect Pagamento
        if ($totalAmount == 0) {
            app(OrderPaymentFinalizer::class)->finalize($order);
            return redirect()->route('checkout.success', ['order_code' => $order->group_code]);
        }

        if ($validatedData['payment_method'] === 'bank_transfer') {
            SendBankTransferInstructions::dispatch($order->id);
            return redirect()->route('booking.show', ['token' => $creation['public_token']]);
        }

        try {
            return ($validatedData['payment_method'] === 'stripe')
                ? $this->processStripePayment($order)
                : $this->processPaypalPayment($order);
        } catch (Throwable $e) {
            $order->update(['status' => 'payment_error']);
            Log::error('Errore avvio pagamento', [
                'order_id' => $order->id,
                'group_code' => $order->group_code,
                'payment_method' => $order->payment_method,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['payment_method' => 'Non siamo riusciti ad avviare il pagamento. Controlla la configurazione del gateway e riprova.']);
        }
    }

    private function processStripePayment(Order $order)
    {
        $this->createOrRetrieveStripePaymentIntent($order);

        return redirect()->route('checkout.payment', ['order_code' => $order->group_code]);
    }

    public function payment($order_code)
    {
        $order = Order::with('tickets')->where('group_code', $order_code)->firstOrFail();

        if ($order->payment_method !== 'stripe') {
            return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Metodo di pagamento non valido per questo ordine.');
        }

        if ($order->status === 'paid') {
            return redirect()->route('checkout.success', ['order_code' => $order->group_code]);
        }

        try {
            $paymentIntent = $this->createOrRetrieveStripePaymentIntent($order);

            if ($paymentIntent->status === 'succeeded') {
                app(OrderPaymentFinalizer::class)->finalize($order);

                return redirect()->route('checkout.success', ['order_code' => $order->group_code]);
            }
        } catch (Throwable $e) {
            $order->update(['status' => 'payment_error']);
            Log::error('Errore creazione Payment Intent Stripe', [
                'order_id' => $order->id,
                'group_code' => $order->group_code,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('iscrizione')
                ->withErrors(['payment_method' => 'Non siamo riusciti ad avviare il pagamento con carta. Riprova tra poco.']);
        }

        return view('checkout.payment', [
            'order' => $order,
            'clientSecret' => $paymentIntent->client_secret,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    private function createOrRetrieveStripePaymentIntent(Order $order)
    {
        $stripeSecret = config('services.stripe.secret');

        if (blank($stripeSecret) || blank(config('services.stripe.key'))) {
            throw new RuntimeException('Stripe keys non configurate.');
        }

        \Stripe\Stripe::setApiKey($stripeSecret);

        if ($order->stripe_payment_intent_id) {
            try {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($order->stripe_payment_intent_id);

                if ($paymentIntent->status !== 'canceled') {
                    return $paymentIntent;
                }
            } catch (Throwable $e) {
                Log::warning('Payment Intent Stripe non recuperabile, ne creo uno nuovo', [
                    'order_id' => $order->id,
                    'group_code' => $order->group_code,
                    'stripe_payment_intent_id' => $order->stripe_payment_intent_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $order->loadMissing('tickets');
        $firstTicket = $order->tickets->first();

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => (int) round($order->total_amount * 100),
            'currency' => 'eur',
            'payment_method_types' => ['card'],
            'description' => 'Iscrizione Pescara Half Marathon 2027 - Ordine ' . $order->group_code,
            'receipt_email' => $firstTicket?->email,
            'metadata' => [
                'order_id' => (string) $order->id,
                'group_code' => $order->group_code,
            ],
        ]);

        $order->update(['stripe_payment_intent_id' => $paymentIntent->id]);

        return $paymentIntent;
    }

    private function processPaypalPayment(Order $order)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('checkout.success', ['order_code' => $order->group_code]),
                "cancel_url" => route('checkout.cancel', ['order' => $order->group_code]),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "EUR",
                        "value" => $order->total_amount
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    $order->update(['paypal_order_id' => $response['id']]); 
                    return redirect()->away($links['href']);
                }
            }
        }

        return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Qualcosa è andato storto con PayPal.');
    }

    public function success(Request $request, $order_code)
    {
        $order = Order::with('tickets')->where('group_code', $order_code)->firstOrFail();

        // 1. Se l'ordine risulta già pagato, mostriamo solo la vista.
        if ($order->status === 'paid') {
            app(OrderPaymentFinalizer::class)->finalize($order);
            return view('checkout.success', compact('order'));
        }

        // --- 2. VERIFICA E CATTURA PAGAMENTO PAYPAL ---
        if ($order->payment_method === 'paypal' && $request->has('token')) {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            // QUESTA È LA RIGA MAGICA CHE PRELEVA EFFETTIVAMENTE I SOLDI
            $response = $provider->capturePaymentOrder($request->query('token'));
            
            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                app(OrderPaymentFinalizer::class)->finalize($order);
            } else {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Il pagamento PayPal non è stato autorizzato o è stato annullato.');
            }
        }
        
        // --- 3. VERIFICA PAGAMENTO STRIPE ---
        elseif ($order->payment_method === 'stripe' && $order->stripe_payment_intent_id) {
            $stripeSecret = config('services.stripe.secret');

            if (blank($stripeSecret)) {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Stripe non è configurato correttamente.');
            }

            \Stripe\Stripe::setApiKey($stripeSecret);
            $paymentIntent = \Stripe\PaymentIntent::retrieve($order->stripe_payment_intent_id);
            
            if ($paymentIntent->status === 'succeeded') {
                app(OrderPaymentFinalizer::class)->finalize($order);
            } else {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Il pagamento Stripe non è stato completato.');
            }
        }
        elseif ($order->payment_method === 'stripe' && $order->stripe_session_id) {
            $stripeSecret = config('services.stripe.secret');

            if (blank($stripeSecret)) {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Stripe non è configurato correttamente.');
            }

            \Stripe\Stripe::setApiKey($stripeSecret);
            $session = \Stripe\Checkout\Session::retrieve($order->stripe_session_id);

            if ($session->payment_status == 'paid') {
                app(OrderPaymentFinalizer::class)->finalize($order);
            } else {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Il pagamento Stripe non è stato completato.');
            }
        }
        
        // --- 4. VERIFICA ORDINI A ZERO EURO (Promo Gruppi) ---
        elseif ($order->total_amount == 0) {
            app(OrderPaymentFinalizer::class)->finalize($order);
        }

        // --- 5. AZIONI POST-PAGAMENTO VERIFICATO ---
        // Se l'ordine ha passato i controlli ed è "paid", attiviamo i biglietti
        if ($order->status === 'paid') {
            app(OrderPaymentFinalizer::class)->finalize($order);
            return view('checkout.success', compact('order'));
        }

        // Fallback in caso di tentativi anomali
        return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Errore nella convalida del pagamento.');
    }

    public function downloadTicket($unique_code)
    {
        $ticket = Ticket::with('order')->where('unique_ticket_code', $unique_code)->firstOrFail();

        if ($ticket->order->status !== 'paid') {
            return back()->with('error', 'Il pagamento non è ancora stato confermato. Riprova tra poco.');
        }

        $pdf = Pdf::loadView('pdf.ticket', compact('ticket'));
        $fileName = 'Biglietto_Pescara_Half_Marathon_' . str_replace(' ', '_', $ticket->last_name) . '.pdf';
        
        return $pdf->download($fileName);
    }
}
