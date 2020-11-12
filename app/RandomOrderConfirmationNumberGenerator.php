<?php

declare(strict_types=1);

namespace App;

class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator
{
    private const CHAR_POOL = '2345679ABCDEFGHJKLMNPQRSTUVWXYZ';
    private const CONFIRMATION_SIZE = 24;

    public function generate(): string
    {
        return substr(str_shuffle(str_repeat(self::CHAR_POOL, self::CONFIRMATION_SIZE)), 0 , self::CONFIRMATION_SIZE);
    }
}
