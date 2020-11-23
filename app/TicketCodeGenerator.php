<?php

declare(strict_types=1);

namespace App;

use App\Models\Ticket;

interface TicketCodeGenerator
{
    public function generateFor(Ticket $ticket): string;
}
