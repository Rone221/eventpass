<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* ─── Comptes de démonstration ─── */
        $organizer = User::create([
            'name' => 'Awa Diop (Organisatrice)',
            'email' => 'organisateur@eventpass.test',
            'phone' => '770000001',
            'role' => UserRole::Organizer,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Moussa Ba (Participant)',
            'email' => 'participant@eventpass.test',
            'phone' => '770000002',
            'role' => UserRole::Participant,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        /* ─── Événements de démonstration ─── */
        $events = [
            [
                'title' => 'DevFest Dakar 2026',
                'description' => "La plus grande conférence tech de l'année.\nIA, Web, Mobile et Cloud au programme.",
                'venue' => 'CICAD',
                'city' => 'Diamniadio',
                'starts_at' => now()->addMonths(2)->setTime(9, 0),
                'ends_at' => now()->addMonths(2)->setTime(18, 0),
                'types' => [
                    ['name' => 'Standard', 'price' => 5000, 'quantity' => 200],
                    ['name' => 'VIP', 'price' => 15000, 'quantity' => 50],
                ],
            ],
            [
                'title' => "Concert Youssou N'Dour",
                'description' => "Une soirée exceptionnelle au Grand Théâtre.",
                'venue' => 'Grand Théâtre National',
                'city' => 'Dakar',
                'starts_at' => now()->addMonth()->setTime(20, 0),
                'ends_at' => now()->addMonth()->setTime(23, 30),
                'types' => [
                    ['name' => 'Balcon', 'price' => 10000, 'quantity' => 300],
                    ['name' => 'Carré Or', 'price' => 25000, 'quantity' => 80],
                ],
            ],
            [
                'title' => 'Startup Weekend Saint-Louis',
                'description' => "54h pour transformer une idée en startup.",
                'venue' => 'Université Gaston Berger',
                'city' => 'Saint-Louis',
                'starts_at' => now()->addWeeks(3)->setTime(17, 0),
                'ends_at' => now()->addWeeks(3)->addDays(2)->setTime(20, 0),
                'types' => [
                    ['name' => 'Participant', 'price' => 3000, 'quantity' => 100],
                ],
            ],
        ];

        foreach ($events as $data) {
            $event = $organizer->events()->create([
                'title' => $data['title'],
                'description' => $data['description'],
                'venue' => $data['venue'],
                'city' => $data['city'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'status' => EventStatus::Published,
            ]);

            foreach ($data['types'] as $type) {
                $event->ticketTypes()->create($type);
            }
        }
    }
}
