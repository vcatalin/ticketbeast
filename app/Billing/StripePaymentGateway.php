<?php

declare(strict_types=1);

namespace App\Billing;

use App\Billing\Exceptions\PaymentFailedException;
use Stripe\Exception\InvalidRequestException;
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
        try {
            $this->stripeClient->charges->create([
                'amount' => $amount,
                'currency' => 'usd',
                'source' => $token,
            ]);
        } catch (InvalidRequestException $exception) {
            throw new PaymentFailedException();
        }
    }
}
