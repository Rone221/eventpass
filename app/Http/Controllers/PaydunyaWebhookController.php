<?php

namespace App\Http\Controllers;

use App\Services\PaydunyaIpnHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Point d'entrée du webhook IPN Paydunya (callback_url).
 *
 * Impératif du sujet : la commande n'est JAMAIS validée sur la page de
 * retour (return_url). Seule cette notification serveur-à-serveur, après
 * vérification de la signature, fait foi.
 */
class PaydunyaWebhookController extends Controller
{
    public function __invoke(Request $request, PaydunyaIpnHandler $handler): Response
    {
        // Paydunya poste les informations sous la clé "data"
        $data = $request->input('data', []);

        $result = $handler->handle(is_array($data) ? $data : []);

        // Signature invalide => 403 ; sinon 200 pour acquitter la réception
        $status = $result === 'invalid' ? 403 : 200;

        return response("IPN: {$result}", $status);
    }
}
