<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventBrowseController;
use App\Http\Controllers\Organizer\EventController;
use App\Http\Controllers\Organizer\ScannerController;
use App\Http\Controllers\Participant\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaydunyaWebhookController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/* ───────────────── Catalogue public ───────────────── */
Route::get('/', [EventBrowseController::class, 'index'])->name('home');
Route::get('/events', [EventBrowseController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventBrowseController::class, 'show'])->name('events.show');

/* ───────────────── Tableau de bord (redirection par rôle) ───────────────── */
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/* ───────────────── Routes authentifiées ───────────────── */
Route::middleware('auth')->group(function () {

    // Profil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* ─── Participant : achat & commandes ─── */
    Route::middleware('role:participant')->group(function () {
        Route::post('/events/{event}/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('/mes-commandes', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/mes-commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/mes-commandes/{order}/billets', [OrderController::class, 'downloadTickets'])->name('orders.tickets');
    });

    /* ─── Paiement : retour / annulation (propriétaire de la commande) ─── */
    Route::get('/paiement/{order}/retour', [PaymentController::class, 'return'])->name('payment.return');
    Route::get('/paiement/{order}/annulation', [PaymentController::class, 'cancel'])->name('payment.cancel');

    // Guichet simulé (mode PAYMENT_FAKE_GATEWAY=true)
    Route::get('/paiement/{order}/guichet', [PaymentController::class, 'fakeShow'])->name('payment.fake.show');
    Route::post('/paiement/{order}/guichet', [PaymentController::class, 'fakePay'])->name('payment.fake.pay');

    /* ─── Organisateur : événements & scanner ─── */
    Route::middleware('role:organizer')->prefix('organisateur')->name('organizer.')->group(function () {
        Route::resource('events', EventController::class);
        Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner');
    });
});

/* ───────────────── Webhook IPN Paydunya (serveur-à-serveur) ───────────────── */
Route::post('/webhooks/paydunya', PaydunyaWebhookController::class)->name('webhooks.paydunya');

require __DIR__.'/auth.php';
