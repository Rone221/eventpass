<?php

return [

    /*
    |--------------------------------------------------------------------------
    |  Configuration Paydunya
    |--------------------------------------------------------------------------
    |  Toutes les clés sensibles proviennent du .env (impératif sécurité du
    |  sujet). NE JAMAIS committer les vraies clés dans le dépôt.
    */

    'mode' => env('PAYDUNYA_MODE', 'test'), // test | live

    'keys' => [
        'master'  => env('PAYDUNYA_MASTER_KEY'),
        'public'  => env('PAYDUNYA_PUBLIC_KEY'),
        'private' => env('PAYDUNYA_PRIVATE_KEY'),
        'token'   => env('PAYDUNYA_TOKEN'),
    ],

    'store' => [
        'name'  => env('PAYDUNYA_STORE_NAME', 'EventPass'),
        'phone' => env('PAYDUNYA_STORE_PHONE'),
    ],

    /*
    | URL de base de l'API Paydunya selon le mode.
    | Sandbox : https://app.paydunya.com/sandbox-api/v1
    | Live    : https://app.paydunya.com/api/v1
    */
    'base_url' => env('PAYDUNYA_MODE', 'test') === 'live'
        ? 'https://app.paydunya.com/api/v1'
        : 'https://app.paydunya.com/sandbox-api/v1',

    /*
    | Mode simulateur : court-circuite les appels réseau Paydunya pour
    | permettre de dérouler tout le tunnel (initiation -> IPN -> billet)
    | en local sans clés ni connexion. Mettre PAYMENT_FAKE_GATEWAY=false
    | pour utiliser le vrai sandbox Paydunya.
    */
    'fake' => env('PAYMENT_FAKE_GATEWAY', false),

    'currency' => 'XOF',
];
