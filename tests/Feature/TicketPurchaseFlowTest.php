<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Mail\TicketsMail;
use App\Models\Order;
use App\Models\User;
use App\Services\PaydunyaIpnHandler;
use App\Services\PaydunyaService;
use App\Services\TicketTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TicketPurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrganizerWithEvent(): array
    {
        $organizer = User::create([
            'name' => 'Org', 'email' => 'org@test.dev', 'role' => UserRole::Organizer,
            'password' => bcrypt('password'),
        ]);

        $event = $organizer->events()->create([
            'title' => 'Test Event', 'venue' => 'Salle', 'city' => 'Dakar',
            'starts_at' => now()->addWeek(), 'status' => EventStatus::Published,
        ]);

        $type = $event->ticketTypes()->create(['name' => 'Standard', 'price' => 5000, 'quantity' => 10]);

        return [$organizer, $event, $type];
    }

    public function test_full_purchase_payment_and_scan_flow(): void
    {
        Mail::fake();
        [$organizer, $event, $type] = $this->makeOrganizerWithEvent();

        $participant = User::create([
            'name' => 'Part', 'email' => 'part@test.dev', 'role' => UserRole::Participant,
            'password' => bcrypt('password'),
        ]);

        /* 1. Tunnel d'achat : crée la commande + redirige vers le guichet */
        $this->actingAs($participant)
            ->post(route('checkout.store', $event), ['quantities' => [$type->id => 2]])
            ->assertRedirect();

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(10000, $order->total_amount);
        $this->assertNotNull($order->payment_token);

        /* 2. Notification IPN signée -> validation de la commande (+ Job sync) */
        $payload = app(PaydunyaService::class)->fakeIpnPayload($order->fresh(), 'completed');
        $result = app(PaydunyaIpnHandler::class)->handle($payload);

        $this->assertSame('paid', $result);
        $order->refresh();
        $this->assertSame(OrderStatus::Paid, $order->status);

        /* 3. Billets générés + stock décrémenté + email envoyé */
        $this->assertSame(2, $order->tickets()->count());
        $this->assertSame(2, $type->fresh()->sold);
        Mail::assertSent(TicketsMail::class);

        /* 4. IPN rejouée = idempotent (pas de billets en double) */
        app(PaydunyaIpnHandler::class)->handle($payload);
        $this->assertSame(2, $order->tickets()->count());

        /* 5. API de scan Sanctum : premier scan valide
              (on coupe la session web pour simuler une requête mobile pure :
               sinon le guard Sanctum retombe sur l'utilisateur de session) */
        $this->app['auth']->forgetGuards();

        $token = $organizer->createToken('scanner', ['ticket:validate'])->plainTextToken;
        $ticket = $order->tickets()->first();
        $qrPayload = app(TicketTokenService::class)->buildPayload($ticket);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.tickets.validate'), ['token' => $qrPayload])
            ->assertOk()
            ->assertJson(['status' => 'valid']);

        $this->assertSame(TicketStatus::Scanned, $ticket->fresh()->status);

        /* 6. Second scan = déjà scanné (anti double-scan) */
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.tickets.validate'), ['token' => $qrPayload])
            ->assertStatus(409)
            ->assertJson(['status' => 'already_scanned']);

        /* 7. QR falsifié = signature rejetée */
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.tickets.validate'), ['token' => 'EVP.1.faketoken.badsignature'])
            ->assertStatus(422)
            ->assertJson(['status' => 'invalid_signature']);
    }

    public function test_ipn_with_invalid_signature_is_rejected(): void
    {
        [$organizer, $event, $type] = $this->makeOrganizerWithEvent();
        $participant = User::create([
            'name' => 'P', 'email' => 'p2@test.dev', 'role' => UserRole::Participant,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($participant)
            ->post(route('checkout.store', $event), ['quantities' => [$type->id => 1]]);
        $order = Order::first();

        // Signature volontairement fausse
        $result = app(PaydunyaIpnHandler::class)->handle([
            'status' => 'completed',
            'hash' => 'mauvais-hash',
            'custom_data' => ['order_reference' => $order->reference],
        ]);

        $this->assertSame('invalid', $result);
        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
        $this->assertSame(0, $order->tickets()->count());
    }

    public function test_organizer_cannot_scan_another_organizers_ticket(): void
    {
        [$organizerA, $eventA, $typeA] = $this->makeOrganizerWithEvent();

        $participant = User::create([
            'name' => 'P', 'email' => 'p3@test.dev', 'role' => UserRole::Participant,
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($participant)
            ->post(route('checkout.store', $eventA), ['quantities' => [$typeA->id => 1]]);
        $order = Order::first();
        app(PaydunyaIpnHandler::class)->handle(
            app(PaydunyaService::class)->fakeIpnPayload($order->fresh(), 'completed')
        );

        // Un autre organisateur tente de scanner
        $this->app['auth']->forgetGuards();
        $organizerB = User::create([
            'name' => 'OrgB', 'email' => 'orgb@test.dev', 'role' => UserRole::Organizer,
            'password' => bcrypt('password'),
        ]);
        $token = $organizerB->createToken('scanner', ['ticket:validate'])->plainTextToken;
        $qrPayload = app(TicketTokenService::class)->buildPayload($order->tickets()->first());

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.tickets.validate'), ['token' => $qrPayload])
            ->assertStatus(403);
    }
}
