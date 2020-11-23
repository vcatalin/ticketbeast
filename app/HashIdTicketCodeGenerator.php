<?php

declare(strict_types=1);

namespace App;

use App\Models\Ticket;
use Hashids\Hashids;
use Hashids\HashidsInterface;

class HashIdTicketCodeGenerator implements TicketCodeGenerator
{
    private const LENGTH = 6;

    private HashidsInterface $hashids;

    public function __construct(string $salt)
    {

        $this->hashids = new Hashids($salt, self::LENGTH, implode('', range('A', 'Z')));
    }

    public function generateFor(Ticket $ticket): string
    {
        return $this->hashids->encode($ticket->id);
    }
}
