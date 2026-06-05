<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaydunyaIpnHandler;
use App\Services\PaydunyaService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Page de retour (return_url) après le guichet de paiement.
     * IMPORTANT : on n'y valide JAMAIS la commande — on affiche seulement
     * l'état courant, mis à jour par le webhook IPN.
     */
    public function return(Order $order)
    {
        $this->authorizeOwner($order);
        $order->load('event', 'items.ticketType');

        return view('payment.return', compact('order'));
    }

    /** Page d'annulation (cancel_url). */
    public function cancel(Order $order)
    {
        $this->authorizeOwner($order);

        return view('payment.cancel', compact('order'));
    }

    /* ─────────── SIMULATEUR LOCAL (mode PAYMENT_FAKE_GATEWAY=true) ─────────── */

    /** Page "guichet" factice imitant Wave / Orange Money. */
    public function fakeShow(Order $order)
    {
        $this->authorizeOwner($order);
        abort_unless(config('paydunya.fake'), 404);

        $order->load('event', 'items.ticketType');

        return view('payment.fake', compact('order'));
    }

    /**
     * Simule le retour du guichet : construit une notification IPN valide
     * (avec signature correcte) et la fait passer par le MÊME gestionnaire
     * que le vrai webhook Paydunya.
     */
    public function fakePay(
        Request $request,
        Order $order,
        PaydunyaService $paydunya,
        PaydunyaIpnHandler $handler,
    ) {
        $this->authorizeOwner($order);
        abort_unless(config('paydunya.fake'), 404);

        $outcome = $request->input('outcome', 'success');
        $status = $outcome === 'success' ? 'completed' : 'cancelled';

        // Notification IPN factice — déclenche vérif. signature + validation
        $payload = $paydunya->fakeIpnPayload($order, $status);
        $handler->handle($payload);

        return redirect()->route('payment.return', $order);
    }

    private function authorizeOwner(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);
    }
}
