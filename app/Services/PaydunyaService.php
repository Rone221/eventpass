<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Intégration Paydunya (Module 2 du sujet).
 *
 *  - createInvoice()  : initialise le paiement et renvoie l'URL du guichet
 *                       (Wave / Orange Money / Free Money / CB).
 *  - verifyIpnHash()  : vérifie la signature de sécurité reçue sur le webhook
 *                       IPN (hash SHA-512 de la Master Key) AVANT de valider
 *                       la commande en base.
 *
 * Un "mode simulateur" (config paydunya.fake) permet de dérouler tout le
 * tunnel sans réseau ni clés, pour la démo et les tests.
 */
class PaydunyaService
{
    /**
     * Initialise une facture de paiement et renvoie le token + l'URL de
     * redirection vers le guichet de paiement.
     *
     * @return array{token:string, redirect_url:string}
     */
    public function createInvoice(Order $order): array
    {
        if (config('paydunya.fake')) {
            return $this->createFakeInvoice($order);
        }

        $payload = [
            'invoice' => [
                'items' => $this->buildItems($order),
                'total_amount' => $order->total_amount,
                'description' => "Billets pour « {$order->event->title} »",
            ],
            'store' => [
                'name' => config('paydunya.store.name'),
                'phone' => config('paydunya.store.phone'),
            ],
            'actions' => [
                'return_url' => route('payment.return', $order),
                'cancel_url' => route('payment.cancel', $order),
                'callback_url' => route('webhooks.paydunya'), // URL IPN
            ],
            // Renvoyé tel quel dans l'IPN : permet de retrouver la commande
            'custom_data' => [
                'order_reference' => $order->reference,
            ],
        ];

        $response = Http::withHeaders($this->headers())
            ->acceptJson()
            ->post(config('paydunya.base_url').'/checkout-invoice/create', $payload);

        $data = $response->json() ?? [];

        if (! $response->ok() || ($data['response_code'] ?? null) !== '00') {
            Log::error('[Paydunya] Échec création facture', ['order' => $order->reference, 'body' => $data]);
            throw new RuntimeException('Impossible d\'initialiser le paiement Paydunya : '.($data['response_text'] ?? 'erreur inconnue'));
        }

        return [
            'token' => $data['token'],
            'redirect_url' => $data['invoice_url'] ?? (config('paydunya.base_url').'/checkout/invoice/'.$data['token']),
        ];
    }

    /**
     * Vérifie l'authenticité d'une notification IPN.
     *
     * Paydunya joint à chaque notification un champ data[hash] égal au
     * SHA-512 de la Master Key. On le recalcule et on compare à temps
     * constant : si ça ne correspond pas, la requête n'émane pas de Paydunya.
     */
    public function verifyIpnHash(array $data): bool
    {
        $received = $data['hash'] ?? null;
        if (! $received) {
            return false;
        }

        $expected = hash('sha512', (string) config('paydunya.keys.master'));

        return hash_equals($expected, $received);
    }

    /**
     * Confirme côté serveur l'état réel d'une facture auprès de Paydunya
     * (confirm-by-token). Renvoie le statut ('completed', 'cancelled', ...).
     * En mode simulateur, on fait confiance au payload reçu.
     */
    public function confirmStatus(string $token): ?string
    {
        if (config('paydunya.fake')) {
            return 'completed';
        }

        $response = Http::withHeaders($this->headers())
            ->acceptJson()
            ->get(config('paydunya.base_url')."/checkout-invoice/confirm/{$token}");

        return $response->json('status');
    }

    /* ───────────────── Helpers ───────────────── */

    private function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'PAYDUNYA-MASTER-KEY' => config('paydunya.keys.master'),
            'PAYDUNYA-PRIVATE-KEY' => config('paydunya.keys.private'),
            'PAYDUNYA-TOKEN' => config('paydunya.keys.token'),
        ];
    }

    private function buildItems(Order $order): array
    {
        $items = [];
        foreach ($order->items as $i => $item) {
            $items["item_{$i}"] = [
                'name' => $item->ticketType->name,
                'quantity' => $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'total_price' => (string) $item->subtotal(),
            ];
        }

        return $items;
    }

    /**
     * Facture simulée : token local + redirection vers une page guichet
     * factice servie par l'application elle-même.
     *
     * @return array{token:string, redirect_url:string}
     */
    private function createFakeInvoice(Order $order): array
    {
        $token = 'FAKE-'.Str::upper(Str::random(20));

        return [
            'token' => $token,
            'redirect_url' => route('payment.fake.show', $order),
        ];
    }

    /**
     * Construit un payload IPN factice valide (utilisé par le simulateur)
     * incluant un hash correct, pour exercer le vrai chemin de vérification.
     */
    public function fakeIpnPayload(Order $order, string $status = 'completed'): array
    {
        return [
            'status' => $status,
            'hash' => hash('sha512', (string) config('paydunya.keys.master')),
            'invoice' => [
                'token' => $order->payment_token,
                'total_amount' => $order->total_amount,
            ],
            'custom_data' => [
                'order_reference' => $order->reference,
            ],
        ];
    }
}
