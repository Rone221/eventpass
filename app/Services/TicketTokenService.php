<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Str;

/**
 * Génère et vérifie les tokens de sécurité embarqués dans les QR codes.
 *
 * Le QR ne contient PAS l'id brut : il contient une charge utile signée
 * de la forme  EVP.<ticketId>.<token>.<signature>
 * où la signature est un HMAC-SHA256 tronqué, calculé avec APP_KEY.
 *
 * Avantages :
 *   - Un QR forgé (mauvaise signature) est rejeté avant même la requête DB.
 *   - Le token aléatoire unique reste la garantie finale (vérifié en base).
 */
class TicketTokenService
{
    private const PREFIX = 'EVP';

    /** Génère un token aléatoire unique pour un billet (stocké en base). */
    public function generateToken(): string
    {
        do {
            $token = Str::random(40);
        } while (Ticket::where('token', $token)->exists());

        return $token;
    }

    /** Construit la charge utile signée à encoder dans le QR code. */
    public function buildPayload(Ticket $ticket): string
    {
        $body = self::PREFIX.'.'.$ticket->id.'.'.$ticket->token;

        return $body.'.'.$this->sign($body);
    }

    /**
     * Vérifie une charge utile scannée et renvoie ['id' => .., 'token' => ..]
     * ou null si le format ou la signature est invalide.
     *
     * @return array{id:int, token:string}|null
     */
    public function parsePayload(string $payload): ?array
    {
        $parts = explode('.', $payload);
        if (count($parts) !== 4) {
            return null;
        }

        [$prefix, $id, $token, $signature] = $parts;

        if ($prefix !== self::PREFIX) {
            return null;
        }

        $body = $prefix.'.'.$id.'.'.$token;

        // Comparaison à temps constant contre les attaques temporelles
        if (! hash_equals($this->sign($body), $signature)) {
            return null;
        }

        return ['id' => (int) $id, 'token' => $token];
    }

    /** HMAC-SHA256 tronqué (32 hex) basé sur la clé applicative. */
    private function sign(string $body): string
    {
        $key = config('app.key');

        return substr(hash_hmac('sha256', $body, $key), 0, 32);
    }
}
