<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /* ───────────────── Relations ───────────────── */

    /** Événements créés (si organisateur) */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /** Commandes passées (si participant) */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /* ───────────────── Helpers de rôle ───────────────── */

    public function isOrganizer(): bool
    {
        return $this->role === UserRole::Organizer;
    }

    public function isParticipant(): bool
    {
        return $this->role === UserRole::Participant;
    }
}
