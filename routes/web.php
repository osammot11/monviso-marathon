<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Models\Setting;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BankTransferController;
use App\Http\Controllers\AdminBankTransferController;
use App\Models\EventCategory;
use App\Models\EventAddon;
use App\Services\BankTransferService;
use App\Services\OrderPricingService;

// Rotta per inviare i dati del modulo (POST)
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Pagamento carta interno con Stripe Elements
Route::get('/checkout/{order_code}/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');

// Rotta per il ritorno DOPO il pagamento confermato (GET)
Route::get('/checkout/success/{order_code}', [CheckoutController::class, 'success'])->name('checkout.success');

// Rotta per il ritorno in caso di ANNULLAMENTO (GET)
Route::get('/checkout/cancel/{order}', function($order) {
    // Per ora lasciamo un testo semplice, poi potremo fare una pagina dedicata
    return "Pagamento annullato per l'ordine: " . $order; 
})->name('checkout.cancel');

// Rotta per il download on-demand del singolo biglietto PDF (GET)
Route::get('/ticket/{unique_code}/download', [CheckoutController::class, 'downloadTicket'])->name('ticket.download');

Route::get('/iscrizione', function (BankTransferService $bankTransfer, OrderPricingService $pricing) {
    // Recuperiamo il prezzo dal database, con il paracadute a 28€ se non esiste
    try {
        $currentPrice = Setting::where('key', 'ticket_price')->value('value') ?? 30.00;
    } catch (Throwable $e) {
        $currentPrice = 30.00;
    }

    // Passiamo la variabile alla vista welcome
    $categories = EventCategory::where('is_active', true)->orderBy('sort_order')->get();
    $categories->each(fn (EventCategory $category) => $category->setAttribute('current_price', $pricing->categoryPrice($category)));
    return view('welcome', [
        'currentPrice' => $currentPrice,
        'categories' => $categories,
        'bankTransferAvailable' => $bankTransfer->isAvailable(),
        'shuttleAddonPrice' => (float) (EventAddon::where('code', 'shuttle')->where('is_active', true)->value('price') ?? 0),
    ]);
})->name('iscrizione');

Route::redirect('/ticketing', '/iscrizione', 301);
Route::redirect('/ticketing/index.php', '/iscrizione', 301);


use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

// Rotte Pubbliche per il Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\WebhookController;

Route::post('/webhook/stripe', [WebhookController::class, 'handleStripe'])->name('webhook.stripe');

Route::get('/prenotazione', [BankTransferController::class, 'index'])->name('booking.index');
Route::post('/prenotazione/cerca', [BankTransferController::class, 'lookup'])->middleware('throttle:10,1')->name('booking.lookup');
Route::get('/prenotazione/accesso/{order}', [BankTransferController::class, 'signedAccess'])->middleware('signed')->name('booking.signed');
Route::get('/prenotazione/{token}', [BankTransferController::class, 'show'])->where('token', '[A-Za-z0-9]{64}')->name('booking.show');
Route::post('/prenotazione/{token}/ricevuta', [BankTransferController::class, 'uploadReceipt'])->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:10,1')->name('booking.receipt');
Route::get('/recupera-codice', [BankTransferController::class, 'recovery'])->name('booking.recovery');
Route::post('/recupera-codice/invia', [BankTransferController::class, 'sendOtp'])->middleware('throttle:5,1')->name('booking.otp.send');
Route::post('/recupera-codice/verifica', [BankTransferController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('booking.otp.verify');

// 🔒 GRUPPO ROTTE PROTETTE (Lucchetto)
Route::middleware('auth')->group(function () {
    
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/bonifici', [AdminBankTransferController::class, 'index'])->name('admin.bank.index');
    Route::post('/admin/bonifici/impostazioni', [AdminBankTransferController::class, 'updateSettings'])->name('admin.bank.settings');
    Route::post('/admin/bonifici/profili', [AdminBankTransferController::class, 'storeProfile'])->name('admin.bank.profiles.store');
    Route::post('/admin/bonifici/importa/anteprima', [AdminBankTransferController::class, 'preview'])->name('admin.bank.import.preview');
    Route::post('/admin/bonifici/importa/conferma', [AdminBankTransferController::class, 'confirmImport'])->name('admin.bank.import.confirm');
    Route::post('/admin/bonifici/{order}/approva', [AdminBankTransferController::class, 'approve'])->name('admin.bank.approve');
    Route::post('/admin/bonifici/{order}/rifiuta', [AdminBankTransferController::class, 'reject'])->name('admin.bank.reject');
    Route::post('/admin/bonifici/{order}/annulla', [AdminBankTransferController::class, 'cancel'])->name('admin.bank.cancel');
    Route::get('/admin/bonifici/{order}/ricevuta', [AdminBankTransferController::class, 'receipt'])->name('admin.bank.receipt');
    Route::post('/admin/price', [AdminController::class, 'updatePrice'])->name('admin.update_price');
    
    Route::get('/admin/pending', [AdminController::class, 'pending'])->name('admin.pending');
    Route::post('/admin/upload-csv', [AdminController::class, 'uploadCsv'])->name('admin.upload_csv');
    Route::post('/admin/ticket/{id}/approve', [AdminController::class, 'approveTicket'])->name('admin.approve_ticket');
    Route::delete('/admin/ticket/{id}/delete', [AdminController::class, 'deleteTicket'])->name('admin.delete_ticket');
    
    Route::get('/admin/ticket/create', [AdminController::class, 'createTicket'])->name('admin.create_ticket_form');
    Route::post('/admin/ticket/store', [AdminController::class, 'storeTicket'])->name('admin.store_ticket');
    
    // Esportazione CSV
    Route::get('/admin/export-csv', [AdminController::class, 'exportCsv'])->name('admin.export_csv');
    
    // Azioni sui singoli biglietti
    Route::get('/admin/ticket/{id}/edit', [AdminController::class, 'editTicket'])->name('admin.edit_ticket');
    Route::post('/admin/ticket/{id}/update', [AdminController::class, 'updateTicketData'])->name('admin.update_ticket');
    Route::get('/admin/ticket/{id}/pdf', [AdminController::class, 'downloadPdf'])->name('admin.download_pdf');
});

Route::get('/', [PageController::class, 'show'])->name('home');
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', 'come-arrivare|contattaci|contatti|cookie-policy|gallery|orario|partnership|percorsi|privacy-policy|regolamento|ricettivita|styleguide|termini-condizioni|come-arrivare\.php|contattaci\.php|contatti\.php|cookie-policy\.php|gallery\.php|orario\.php|partnership\.php|percorsi\.php|privacy-policy\.php|regolamento\.php|ricettivita\.php|styleguide\.php|termini-condizioni\.php');
