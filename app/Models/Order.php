<?php

namespace App\Models;

use App\Reservation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function forTickets(Collection $tickets, string $email, int $amount): Order
    {
        $order = self::create([
            'email' => $email,
            'amount' => $amount,
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public static function fromReservation(Reservation $reservation): Order
    {
        $order = self::create([
            'email' => $reservation->email(),
            'amount' => $reservation->totalCost(),
        ]);

        foreach ($reservation->tickets() as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    public function ticketQuantity(): int
    {
        return $this->tickets()->count();
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount,
        ];
    }
}
