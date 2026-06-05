<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API de scan (Module 3 du sujet).
 *
 * POST /api/v1/tickets/validate
 *   - protégée par Sanctum (token de l'app mobile organisateur)
 *   - reçoit la charge utile du QR code
 *   - vérifie : signature, existence, billet payé, NON déjà scanné
 *   - bascule disponible -> scanné de façon ATOMIQUE (anti double-scan)
 */
class TicketValidationController extends Controller
{
    public function __construct(
        private readonly TicketTokenService $tokens,
    ) {}

    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        // 1. Vérification de la signature embarquée dans le QR
        $parsed = $this->tokens->parsePayload($request->input('token'));

        if ($parsed === null) {
            return $this->reject('invalid_signature', 'QR code invalide ou falsifié.', 422);
        }

        // 2. Recherche du billet (id + token doivent concorder)
        $ticket = Ticket::with(['event', 'ticketType', 'order'])
            ->whereKey($parsed['id'])
            ->where('token', $parsed['token'])
            ->first();

        if (! $ticket) {
            return $this->reject('not_found', 'Billet introuvable.', 404);
        }

        // 3. L'organisateur ne peut scanner que SES propres événements
        if ($ticket->event->organizer_id !== $request->user()->id) {
            return $this->reject('forbidden', 'Ce billet ne concerne pas vos événements.', 403);
        }

        // 4. La commande doit être payée
        if (! $ticket->order->isPaid()) {
            return $this->reject('not_paid', 'Billet non payé.', 402);
        }

        // 5. Anti double-scan : bascule atomique disponible -> scanné
        $updated = Ticket::whereKey($ticket->id)
            ->where('status', TicketStatus::Available->value)
            ->update([
                'status' => TicketStatus::Scanned,
                'scanned_at' => now(),
                'scanned_by' => $request->user()->id,
            ]);

        if ($updated === 0) {
            // Déjà scanné : on renvoie quand-même les infos du premier scan
            $ticket->refresh();

            return response()->json([
                'status' => 'already_scanned',
                'message' => 'Billet déjà scanné.',
                'scanned_at' => $ticket->scanned_at?->toIso8601String(),
                'ticket' => $this->ticketPayload($ticket),
            ], 409);
        }

        $ticket->refresh()->load('ticketType', 'event');

        return response()->json([
            'status' => 'valid',
            'message' => 'Billet valide — entrée autorisée.',
            'ticket' => $this->ticketPayload($ticket),
        ]);
    }

    private function ticketPayload(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'holder_name' => $ticket->holder_name,
            'type' => $ticket->ticketType->name,
            'event' => $ticket->event->title,
            'status' => $ticket->status->value,
        ];
    }

    private function reject(string $status, string $message, int $code): JsonResponse
    {
        return response()->json(['status' => $status, 'message' => $message], $code);
    }
}
