<?php

declare(strict_types=1);

namespace App\Facades;

use App\TicketCodeGenerator;
use Illuminate\Support\Facades\Facade;

class TicketCode extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TicketCodeGenerator::class;
    }

    protected static function getMockableClass(): ?string
    {
        return static::getFacadeAccessor();
    }
}
