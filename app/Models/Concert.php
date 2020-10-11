<?php

declare(strict_types = 1);

namespace App\Models;

use App\Billing\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function hasOrderFor(string $customerEmail): bool
    {
        return $this->orders()->where('email', $customerEmail)->count() > 0;
    }

    public function ordersFor(string $customerEmail): Collection
    {
        return $this->orders()->where('email', $customerEmail)->get();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets(string $email, int $ticketQuantity): Order
    {
        /** @var Ticket $tickets */
        $tickets = $this->tickets()->available()->take($ticketQuantity)->get();

        if ($tickets->count() < $ticketQuantity) {
            throw new NotEnoughTicketsException();
        }

        /** @var Order $order */
        $order = $this->orders()->create(['email' => $email,]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
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
}
