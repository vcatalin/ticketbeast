<?php

declare(strict_types = 1);

namespace App\Billing;

use App\Billing\Exceptions\PaymentFailedException;
use Closure;
use Illuminate\Support\Collection;

interface PaymentGateway
{
    /**
     * @throws PaymentFailedException
     */
    public function charge(int $amount, string $token): Charge;
    public function getValidTestToken(string $cardNumber): string;
    public function newChargesDuring(Closure $param): Collection;
}
