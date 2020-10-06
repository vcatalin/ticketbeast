<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function orderTickets(string $email, int $ticketQuantity)
    {
        $order = $this->orders()->create([
            'email' => $email,
        ]);

        foreach (range(1, $ticketQuantity) as $item) {
            $order->tickets()->create([]);
        }

        return $order;
    }
}
