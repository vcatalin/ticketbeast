<?php

declare(strict_types = 1);

namespace App\Billing;

use App\Billing\Exceptions\PaymentFailedException;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{
    protected Collection $charges;
    protected Collection $tokens;
    private Closure $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = new Collection();
        $this->tokens = new Collection();
    }

    public function getValidTestToken(string $cardNumber = '4242424242424242'): string
    {
        $token = 'fake-tok_' . Str::random(24);
        $this->tokens[$token] = $cardNumber;

        return $token;
    }

    public function charge(int $amount, string $token)
    {
        if (!empty($this->beforeFirstChargeCallback)) {
            $callback = $this->beforeFirstChargeCallback;
            unset($this->beforeFirstChargeCallback);
            $callback($this);
        }

        if (!$this->tokens->has($token)) {
            throw new PaymentFailedException();
        }
        return $this->charges[] = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens[$token], -4),
        ]);
    }

    public function totalCharges(): int
    {
        return $this->charges->map->amount()->sum();
    }

    public function beforeFirstCharge(Closure $hook): void
    {
        $this->beforeFirstChargeCallback = $hook;
    }

    public function newChargesDuring(Closure $closure): Collection
    {
        $chargesFrom = $this->charges->count();
        $closure();
        return $this->charges->slice($chargesFrom)->reverse()->values();
    }
}
