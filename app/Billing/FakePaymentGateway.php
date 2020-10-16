<?php

declare(strict_types = 1);

namespace App\Billing;

use App\Billing\Exceptions\PaymentFailedException;
use Closure;

class FakePaymentGateway implements PaymentGateway
{
    protected $charges;
    private Closure $beforeFirstChargeCallback;

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
        if (!empty($this->beforeFirstChargeCallback)) {
            $callback = $this->beforeFirstChargeCallback;
            unset($this->beforeFirstChargeCallback);
            $callback($this);
        }

        if ($token !== $this->getValidTestToken()) {
            throw new PaymentFailedException();
        }
        $this->charges[] = $amount;
    }

    public function totalCharges(): int
    {
        return $this->charges->sum();
    }

    public function beforeFirstCharge(Closure $hook): void
    {
        $this->beforeFirstChargeCallback = $hook;
    }
}
