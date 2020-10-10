<?php

declare(strict_types = 1);

namespace App\Billing;

use App\Billing\Exceptions\PaymentFailedException;

interface PaymentGateway
{
    /**
     * @throws PaymentFailedException
     */
    public function charge(int $amount, string $token): void;
}
