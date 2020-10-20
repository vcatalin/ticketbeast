<?php

declare(strict_types=1);

namespace App;

use App\Models\Order;
use Illuminate\Support\Collection;

class Reservation
{
    private Collection $tickets;
    private string $email;

    public function __construct(Collection $tickets, string $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function totalCost(): int
    {
        return $this->tickets->sum('price');
    }

    public function cancel(): void
    {
        foreach($this->tickets as $ticket) {
            $ticket->release();
        }
    }

    public function tickets(): Collection
    {
        return $this->tickets;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function complete(): Order
    {
        return Order::forTickets($this->tickets(), $this->email(), $this->totalCost());
    }
}
