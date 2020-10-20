<?php

declare(strict_types=1);

namespace App\Billing;

use Stripe\StripeClientInterface;

class StripePaymentGateway implements PaymentGateway
{
    private StripeClientInterface $stripeClient;

    public function __construct(StripeClientInterface $stripeClient)
    {
        $this->stripeClient = $stripeClient;
    }

    public function charge(int $amount, string $token): void
    {
        $this->stripeClient->charges->create([
            'amount' => $amount,
            'currency' => 'usd',
            'source' => $token,
        ]);
    }
}
