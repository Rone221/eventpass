<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Génère le PDF des billets d'une commande, chaque billet portant un
 * QR code unique (charge utile signée fournie par TicketTokenService).
 */
class TicketPdfService
{
    public function __construct(
        private readonly TicketTokenService $tokens,
    ) {}

    /**
     * Construit le PDF (binaire) regroupant tous les billets d'une commande.
     */
    public function render(Order $order): string
    {
        $order->loadMissing(['event', 'tickets.ticketType']);

        $tickets = $order->tickets->map(fn (Ticket $ticket) => [
            'model' => $ticket,
            'qr' => $this->qrDataUri($this->tokens->buildPayload($ticket)),
        ]);

        $pdf = Pdf::loadView('tickets.pdf', [
            'order' => $order,
            'event' => $order->event,
            'tickets' => $tickets,
        ])->setPaper('a4');

        return $pdf->output();
    }

    /** Génère un QR code PNG encodé en data-URI, embarquable dans le HTML. */
    private function qrDataUri(string $payload): string
    {
        $qr = new QrCode(data: $payload, size: 220, margin: 8);

        return (new PngWriter())->write($qr)->getDataUri();
    }
}
