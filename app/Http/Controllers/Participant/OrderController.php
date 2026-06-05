<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TicketPdfService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /** Mes commandes. */
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with('event')
            ->withCount('tickets')
            ->latest()
            ->get();

        return view('participant.orders.index', compact('orders'));
    }

    /** Détail d'une commande (+ billets si payée). */
    public function show(Order $order)
    {
        $this->authorizeOwner($order);
        $order->load('event', 'items.ticketType', 'tickets.ticketType');

        return view('participant.orders.show', compact('order'));
    }

    /** Téléchargement du PDF des billets. */
    public function downloadTickets(Order $order, TicketPdfService $pdfService): StreamedResponse
    {
        $this->authorizeOwner($order);
        abort_unless($order->isPaid() && $order->tickets()->exists(), 404, 'Billets non disponibles.');

        $pdf = $pdfService->render($order);

        return response()->streamDownload(
            fn () => print($pdf),
            "billets-{$order->reference}.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }

    private function authorizeOwner(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);
    }
}
