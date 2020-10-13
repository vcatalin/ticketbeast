<?php

declare(strict_types = 1);

namespace App\Models;

use App\Billing\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Concert extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['date'];

    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute(): string
    {
        return $this->date->format('g:ia');
    }

    public function getFormattedTicketPriceAttribute(): string
    {
        return number_format($this->ticket_price / 100, 2);
    }

    /**
     * Scope a query to only published concerts.
     * Concert::published()->get()
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'tickets');
    }

    public function hasOrderFor(string $customerEmail): bool
    {
        return $this->orders()->where('email', $customerEmail)->count() > 0;
    }

    public function ordersFor(string $customerEmail): Collection
    {
        return $this->orders()->where('email', $customerEmail)->get();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets(string $email, int $ticketQuantity): Order
    {
        $tickets = $this->findTickets($ticketQuantity);
        return $this->createOrder($email, $tickets);

    }

    public function addTickets(int $ticketQuantity): Concert
    {
        foreach (range(1, $ticketQuantity) as $item) {
            $this->tickets()->create([]);
        }

        return $this;
    }

    public function ticketsRemaining(): int
    {
        return $this->tickets()->available()->count();
    }

    /**
     * @throws NotEnoughTicketsException
     */
    public function findTickets(int $ticketQuantity): Collection
    {
        /** @var Collection $tickets */
        $tickets = $this->tickets()->available()->take($ticketQuantity)->get();

        if ($tickets->count() < $ticketQuantity) {
            throw new NotEnoughTicketsException();
        }

        return $tickets;
    }

    public function createOrder(string $email, Collection $tickets): Order
    {
        /** @var Order $order */
        $order = Order::create([
            'email' => $email,
            'amount' => $tickets->sum('price'),
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }
}
