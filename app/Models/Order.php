<?php

namespace App\Models;

use App\Facades\OrderConfirmationNumber;
use App\OrderConfirmationNumberGenerator;
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
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'email' => $email,
            'amount' => $amount,
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public static function findByConfirmationNumber(string $confirmationNumber): Order
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
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
            'confirmation_number' => $this->confirmation_number,
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount,
        ];
    }
}
