<?php

namespace App\Jobs;

use App\Enums\TicketStatus;
use App\Mail\TicketsMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\TicketPdfService;
use App\Services\TicketTokenService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Module 3 du sujet : une fois le paiement validé (via le webhook IPN),
 * on génère en arrière-plan les billets PDF (avec QR code signé) puis on
 * les envoie par email à l'acheteur.
 *
 * Le job est idempotent : si les billets existent déjà, il ne fait rien.
 */
class GenerateTicketsForOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(TicketTokenService $tokens, TicketPdfService $pdfService): void
    {
        $order = Order::with('items.ticketType')->find($this->orderId);

        if (! $order || ! $order->isPaid()) {
            return;
        }

        // Idempotence : billets déjà émis ?
        if ($order->tickets()->exists()) {
            return;
        }

        // 1. Création des billets + décrément du stock, de façon atomique
        DB::transaction(function () use ($order, $tokens) {
            foreach ($order->items as $item) {
                /** @var OrderItem $item */
                for ($i = 0; $i < $item->quantity; $i++) {
                    $order->tickets()->create([
                        'ticket_type_id' => $item->ticket_type_id,
                        'event_id' => $order->event_id,
                        'token' => $tokens->generateToken(),
                        'holder_name' => $order->customer_name,
                        'status' => TicketStatus::Available,
                    ]);
                }

                // Incrément du compteur de billets vendus
                $item->ticketType()->increment('sold', $item->quantity);
            }
        });

        // 2. Génération du PDF (hors transaction : opération lourde)
        $order->load('tickets.ticketType', 'event');
        $pdf = $pdfService->render($order);

        // 3. Envoi de l'email avec le PDF en pièce jointe
        Mail::to($order->customer_email)->send(new TicketsMail($order, $pdf));
    }
}
