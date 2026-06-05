<?php

namespace App\Services;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Jobs\GenerateTicketsForOrder;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payment;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Crée une commande "en attente" pour un participant.
     *
     * @param  array<int,int>  $selections  [ticket_type_id => quantité]
     *
     * @throws ValidationException
     */
    public function createOrder(User $user, Event $event, array $selections): Order
    {
        // On ne garde que les quantités strictement positives
        $selections = array_filter($selections, fn ($qty) => (int) $qty > 0);

        if (empty($selections)) {
            throw ValidationException::withMessages([
                'tickets' => 'Sélectionnez au moins un billet.',
            ]);
        }

        if ($event->status !== EventStatus::Published) {
            throw ValidationException::withMessages([
                'tickets' => 'Cet événement n\'est pas ouvert à la vente.',
            ]);
        }

        return DB::transaction(function () use ($user, $event, $selections) {
            $total = 0;
            $itemsData = [];

            foreach ($selections as $ticketTypeId => $qty) {
                $qty = (int) $qty;

                /** @var TicketType $type */
                $type = TicketType::where('event_id', $event->id)
                    ->where('id', $ticketTypeId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($qty > $type->remaining()) {
                    throw ValidationException::withMessages([
                        'tickets' => "Plus assez de places pour « {$type->name} » (reste {$type->remaining()}).",
                    ]);
                }

                $total += $qty * $type->price;
                $itemsData[] = [
                    'ticket_type_id' => $type->id,
                    'quantity' => $qty,
                    'unit_price' => $type->price,
                ];
            }

            $order = Order::create([
                'reference' => $this->uniqueReference(),
                'user_id' => $user->id,
                'event_id' => $event->id,
                'total_amount' => $total,
                'status' => OrderStatus::Pending,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone,
            ]);

            foreach ($itemsData as $data) {
                $order->items()->create($data);
            }

            return $order->load('items.ticketType', 'event');
        });
    }

    /**
     * Valide une commande suite à une notification de paiement confirmée.
     * Idempotent : un second appel (IPN rejoué) ne refait rien.
     */
    public function markAsPaid(Order $order, string $provider, array $payload = []): void
    {
        DB::transaction(function () use ($order, $provider, $payload) {
            // Verrou + relecture pour éviter les doublons en cas d'IPN concurrents
            $fresh = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $fresh || $fresh->status === OrderStatus::Paid) {
                return; // déjà traitée
            }

            $fresh->update([
                'status' => OrderStatus::Paid,
                'payment_provider' => $provider,
                'paid_at' => now(),
            ]);

            Payment::create([
                'order_id' => $fresh->id,
                'provider' => $provider,
                'provider_token' => $fresh->payment_token,
                'status' => PaymentStatus::Completed,
                'amount' => $fresh->total_amount,
                'payload' => $payload,
            ]);

            // Génération des billets + email en arrière-plan (queue)
            GenerateTicketsForOrder::dispatch($fresh->id);
        });
    }

    /** Marque une commande comme échouée/annulée. */
    public function markAsFailed(Order $order, string $provider, array $payload = []): void
    {
        if ($order->isPaid()) {
            return; // ne jamais rétrograder une commande déjà payée
        }

        $order->update([
            'status' => OrderStatus::Failed,
            'payment_provider' => $provider,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => $provider,
            'provider_token' => $order->payment_token,
            'status' => PaymentStatus::Failed,
            'amount' => $order->total_amount,
            'payload' => $payload,
        ]);
    }

    private function uniqueReference(): string
    {
        do {
            $ref = 'EVP-'.Str::upper(Str::random(8));
        } while (Order::where('reference', $ref)->exists());

        return $ref;
    }
}
