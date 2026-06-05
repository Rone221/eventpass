<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = [
        'order_id',
        'ticket_type_id',
        'event_id',
        'token',
        'holder_name',
        'status',
        'scanned_at',
        'scanned_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'scanned_at' => 'datetime',
        ];
    }

    /* ───────────────── Relations ───────────────── */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    /* ───────────────── Helpers ───────────────── */

    public function isScanned(): bool
    {
        return $this->status === TicketStatus::Scanned;
    }
}
