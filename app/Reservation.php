<?php

declare(strict_types=1);

namespace App;

use Illuminate\Support\Collection;

class Reservation
{
    private Collection $tickets;

    public function __construct(Collection $tickets)
    {
        $this->tickets = $tickets;
    }

    public function totalCost(): int
    {
        return $this->tickets->sum('price');
    }
}