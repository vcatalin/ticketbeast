<?php

declare(strict_types = 1);

namespace App\Models;

use App\Billing\Exceptions\NotEnoughTicketsException;
use App\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    public function publish(): void
    {
        $this->update(['published_at' => $this->freshTimestamp()]);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'tickets');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function addTickets(int $ticketQuantity): Concert
    {
        foreach (range(1, $ticketQuantity) as $item) {
            $this->tickets()->create();
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

    /**
     * @throws NotEnoughTicketsException
     */
    public function reserveTickets(int $ticketQuantity, string $email): Reservation
    {
        $tickets = $this->findTickets($ticketQuantity)->each(function (Ticket $ticket) {
            $ticket->reserve();
        });
        return new Reservation($tickets, $email);
    }
}
