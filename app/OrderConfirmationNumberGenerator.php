<?php

declare(strict_types=1);

namespace App;

interface OrderConfirmationNumberGenerator
{
    public function generate(): string;
}
