<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Services\OrderService;
use App\Services\PaydunyaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Tunnel d'achat : crée la commande puis initialise le paiement Paydunya
 * et redirige le participant vers le guichet (Wave / OM / Free Money / CB).
 */
class CheckoutController extends Controller
{
    public function store(
        Request $request,
        Event $event,
        OrderService $orderService,
        PaydunyaService $paydunya,
    ) {
        abort_unless($event->isPublished(), 404);

        $validated = $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'integer', 'min:0', 'max:10'],
        ]);

        // 1. Création de la commande "en attente" (vérifie le stock)
        $order = $orderService->createOrder(
            $request->user(),
            $event,
            $validated['quantities'],
        );

        // 2. Initialisation du paiement
        try {
            $invoice = $paydunya->createInvoice($order);
        } catch (\Throwable $e) {
            Log::error('[Checkout] Échec initialisation paiement', ['error' => $e->getMessage()]);

            return back()->withErrors([
                'tickets' => "Le paiement n'a pas pu être initialisé. Réessayez.",
            ]);
        }

        // 3. On mémorise le token fournisseur + trace le paiement (en attente)
        $order->update([
            'payment_token' => $invoice['token'],
            'payment_provider' => config('paydunya.fake') ? 'fake' : 'paydunya',
        ]);

        $order->payments()->create([
            'provider' => config('paydunya.fake') ? 'fake' : 'paydunya',
            'provider_token' => $invoice['token'],
            'status' => PaymentStatus::Pending,
            'amount' => $order->total_amount,
        ]);

        // 4. Redirection vers le guichet de paiement
        return redirect()->away($invoice['redirect_url']);
    }
}
