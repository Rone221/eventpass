<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private function organizer(): User
    {
        return User::create([
            'name' => 'Org', 'email' => 'org@test.dev', 'role' => UserRole::Organizer,
            'password' => bcrypt('password'),
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Mon événement',
            'venue' => 'Salle A',
            'city' => 'Dakar',
            'starts_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'status' => 'published',
            'ticket_types' => [['name' => 'Standard', 'price' => 1000, 'quantity' => 10]],
        ], $overrides);
    }

    public function test_organizer_can_upload_a_cover_image(): void
    {
        Storage::fake('public');
        $organizer = $this->organizer();

        $this->actingAs($organizer)->post(route('organizer.events.store'), $this->payload([
            'image' => UploadedFile::fake()->image('cover.jpg', 1200, 600),
        ]))->assertRedirect();

        $event = Event::first();
        $this->assertNotNull($event->image_path);
        Storage::disk('public')->assertExists($event->image_path);
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        Storage::fake('public');
        $organizer = $this->organizer();

        $this->actingAs($organizer)->post(route('organizer.events.store'), $this->payload([
            'image' => UploadedFile::fake()->create('virus.pdf', 100, 'application/pdf'),
        ]))->assertSessionHasErrors('image');

        $this->assertSame(0, Event::count());
    }

    public function test_uploading_a_new_image_replaces_the_old_one(): void
    {
        Storage::fake('public');
        $organizer = $this->organizer();

        $this->actingAs($organizer)->post(route('organizer.events.store'), $this->payload([
            'image' => UploadedFile::fake()->image('first.jpg'),
        ]));
        $event = Event::first();
        $old = $event->image_path;

        $this->actingAs($organizer)->put(route('organizer.events.update', $event), $this->payload([
            'image' => UploadedFile::fake()->image('second.jpg'),
        ]));

        $event->refresh();
        $this->assertNotSame($old, $event->image_path);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($event->image_path);
    }
}
