<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Traite une notification de paiement Paydunya (IPN).
 *
 * Chemin unique partagé par le vrai webhook ET le simulateur local, afin que
 * la vérification de signature et la validation de commande soient TOUJOURS
 * exercées de la même façon.
 */
class PaydunyaIpnHandler
{
    public function __construct(
        private readonly PaydunyaService $paydunya,
        private readonly OrderService $orders,
    ) {}

    /**
     * @param  array<string,mixed>  $data  Contenu de data[...] de l'IPN
     * @return string  Résultat : 'paid' | 'failed' | 'invalid' | 'ignored'
     */
    public function handle(array $data): string
    {
        // 1. Vérification de la signature de sécurité (hash SHA-512 master key)
        if (! $this->paydunya->verifyIpnHash($data)) {
            Log::warning('[Paydunya IPN] Signature invalide — requête ignorée.');

            return 'invalid';
        }

        // 2. Retrouver la commande via la donnée personnalisée renvoyée
        $reference = data_get($data, 'custom_data.order_reference');
        $order = $reference ? Order::where('reference', $reference)->first() : null;

        if (! $order) {
            Log::warning('[Paydunya IPN] Commande introuvable.', ['reference' => $reference]);

            return 'ignored';
        }

        // 3. Valider/échouer la commande selon le statut Paydunya
        $status = data_get($data, 'status');

        if ($status === 'completed') {
            $this->orders->markAsPaid($order, 'paydunya', $data);

            return 'paid';
        }

        $this->orders->markAsFailed($order, 'paydunya', $data);

        return 'failed';
    }
}
