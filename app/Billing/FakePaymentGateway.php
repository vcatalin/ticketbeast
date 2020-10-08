<?php

declare(strict_types = 1);

namespace App\Billing;

use App\Billing\Exeptions\PaymentFailedException;

class FakePaymentGateway implements PaymentGateway
{
    protected $charges;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken(): string
    {
        return 'valid-token';
    }

    public function charge(int $amount, string $token): void
    {
        if ($token !== $this->getValidTestToken()) {
            throw new PaymentFailedException();
        }
        $this->charges[] = $amount;
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }
}
