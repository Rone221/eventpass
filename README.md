# 🎟️ EventPass — Plateforme SaaS de billetterie événementielle

> Projet 2 du sujet « Développement web 2.0 » — ESTM Master 1.
> Clone fonctionnel d'Eventbrite : les **organisateurs** créent des événements et
> vendent des billets électroniques ; les **participants** achètent via Mobile Money
> (Wave, Orange Money, Free Money) ou carte bancaire grâce à **Paydunya**.

---

## 1. Stack technique

| Domaine | Choix |
|---|---|
| Framework | **Laravel 12** (PHP 8.2+) |
| Authentification | **Laravel Breeze** (Blade) |
| Base de données | **SQLite** (zéro configuration) |
| Paiement | **Paydunya** (API HTTP via `Illuminate\Support\Facades\Http`) + webhook IPN |
| API mobile | **Laravel Sanctum** (tokens) |
| Billets | PDF (`barryvdh/laravel-dompdf`) + QR code (`endroid/qr-code`) |
| Tâches asynchrones | **Queue** Laravel (driver `database`) |
| Emails | Mailable Markdown (driver `log` par défaut) |

---

## 2. Les 3 modules du sujet

### Module 1 — Authentification & rôles
- Inscription Breeze étendue avec **choix du rôle** : `organizer` ou `participant`
  (+ numéro de téléphone pour le Mobile Money).
- Middleware **`role:`** (`app/Http/Middleware/EnsureUserHasRole.php`) qui protège
  les routes, et **`EventPolicy`** qui garantit qu'un organisateur ne gère que ses
  propres événements.
- Redirection automatique vers le bon tableau de bord selon le rôle.

### Module 2 — Intégration Paydunya
- **Initialisation du paiement** : `PaydunyaService::createInvoice()` envoie le panier
  à Paydunya et récupère l'URL de redirection vers le guichet.
- **Webhook IPN** (`POST /webhooks/paydunya`) : la commande **n'est jamais validée sur
  la page de retour** (`return_url`). Seule la notification serveur-à-serveur, après
  **vérification de la signature** (hash SHA-512 de la Master Key), valide la commande
  en base — `PaydunyaIpnHandler`.
- **Sécurité** : toutes les clés API sont dans le `.env` (jamais committées).
- **Mode simulateur** (`PAYMENT_FAKE_GATEWAY=true`) : permet de dérouler tout le tunnel
  (initiation → IPN signée → billet) **sans réseau ni clés**, idéal pour la démo. La
  notification simulée passe par **le même** gestionnaire que le vrai webhook.

### Module 3 — Génération des billets & API de validation
- Une fois le paiement validé par le webhook, un **Job en queue**
  (`GenerateTicketsForOrder`) génère en arrière-plan le **PDF des billets** (un QR code
  unique par billet, contenant un **token signé HMAC**) puis l'**envoie par email**.
- **API de scan** protégée par Sanctum :
  `POST /api/v1/tickets/validate` — vérifie la signature, l'existence, le paiement, et
  qu'il **n'a pas déjà été scanné**. La bascule `disponible → scanné` est **atomique**
  (anti double-scan).

---

## 3. Architecture du code

```
app/
├── Enums/                  UserRole, EventStatus, OrderStatus, TicketStatus, PaymentStatus
├── Models/                 User, Event, TicketType, Order, OrderItem, Ticket, Payment
├── Services/
│   ├── PaydunyaService.php       Création facture + vérif. signature IPN (+ mode simulé)
│   ├── PaydunyaIpnHandler.php    Traitement IPN partagé (webhook réel ET simulateur)
│   ├── OrderService.php          Création commande + validation/échec paiement
│   ├── TicketTokenService.php    Génération / vérification des tokens QR signés
│   └── TicketPdfService.php      Rendu PDF + QR codes
├── Jobs/GenerateTicketsForOrder.php   Émission billets + email (queue)
├── Mail/TicketsMail.php
├── Http/
│   ├── Middleware/EnsureUserHasRole.php
│   └── Controllers/        (public, Organizer/, Participant/, Api/, Payment, Webhook)
└── Policies/EventPolicy.php
```

---

## 4. Installation

```bash
# 1. Dépendances
composer install
npm install && npm run build

# 2. Configuration
cp .env.example .env
php artisan key:generate

# 3. Base de données + données de démo
php artisan migrate:fresh --seed

# 4. Lancer l'application (2 terminaux)
php artisan serve            # http://localhost:8000
php artisan queue:work       # indispensable : génère les billets après paiement
```

> Le `.env` est livré préconfiguré en **mode simulateur** (`PAYMENT_FAKE_GATEWAY=true`)
> pour une démo immédiate sans clés Paydunya.

### Comptes de démonstration (mot de passe : `password`)

| Rôle | Email |
|---|---|
| Organisateur | `organisateur@eventpass.test` |
| Participant | `participant@eventpass.test` |

---

## 5. Scénario de démonstration

1. Connecté en **participant**, ouvrir un événement → choisir des billets → **Payer**.
2. Sur le guichet simulé, cliquer **« Simuler un paiement réussi »**.
   → une notification IPN **signée** est envoyée au webhook, qui valide la commande.
3. Le **Job** génère le PDF (QR codes) et « envoie » l'email
   (visible dans `storage/logs/laravel.log` avec le driver `log`).
4. Télécharger les billets depuis **Mes commandes**.
5. Connecté en **organisateur**, ouvrir **📷 Scanner**, coller la charge utile du QR
   (ou scanner à la caméra) → 1er scan **valide**, 2e scan **déjà scanné**.

---

## 6. API REST (application mobile de scan)

```http
POST /api/v1/auth/token
     { "email", "password", "device_name" }      → { token, user }

POST /api/v1/tickets/validate      (Authorization: Bearer <token>)
     { "token": "EVP.<id>.<token>.<signature>" }

   200 valid             → entrée autorisée
   409 already_scanned   → billet déjà utilisé
   422 invalid_signature → QR falsifié
   402 not_paid · 404 not_found · 403 forbidden
```

### Passer au vrai sandbox Paydunya
Renseigner les clés dans `.env` puis `PAYMENT_FAKE_GATEWAY=false`. Pour recevoir le
webhook en local, exposer le serveur (ex. `ngrok http 8000`) afin que Paydunya
puisse appeler `callback_url`.

---

## 7. Tests

```bash
php artisan test --filter=TicketPurchaseFlowTest
```

Le test bout-en-bout couvre : achat → IPN signée → génération billets → décrément du
stock → email → idempotence (IPN rejouée) → scan API (valide / double-scan / signature
falsifiée / cross-organisateur).
