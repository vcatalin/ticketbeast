<?php

declare(strict_types=1);

namespace App\Billing;

use App\Billing\Exceptions\PaymentFailedException;
use Closure;
use Illuminate\Support\Collection;
use Stripe\Charge as StripeCharge;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClientInterface;

class StripePaymentGateway implements PaymentGateway
{
    private StripeClientInterface $stripeClient;

    public function __construct(StripeClientInterface $stripeClient)
    {
        $this->stripeClient = $stripeClient;
    }

    public function charge(int $amount, string $token): Charge
    {
        try {
            /** @var StripeCharge $stripeCharge */
            $stripeCharge = $this->stripeClient->charges->create([
                'amount' => $amount,
                'currency' => 'usd',
                'source' => $token,
            ]);

            return new Charge([
                'amount' => $stripeCharge->amount,
                'card_last_four' => $stripeCharge->payment_method_details->card->last4,
            ]);
        } catch (InvalidRequestException $exception) {
            throw new PaymentFailedException();
        }
    }

    public function getValidTestToken(): string
    {
        return $this->stripeClient->tokens->create(
            [
                'card' => [
                    'number' => '4242424242424242',
                    'exp_month' => 1,
                    'exp_year' => date('Y') + 1,
                    'cvc' => '123',
                ],
            ]
        )->id;
    }

    public function newChargesDuring(Closure $callback): Collection
    {
        $latestCharge = $this->lastCharge();
        $callback();
        return $this->newChargesSince($latestCharge)->map(function ($stripeCharge) {
            return new Charge([
                    'amount' => $stripeCharge->amount,
                    'card_last_four' => $stripeCharge->payment_method_details->card->last4,
                ]
            );
        });
    }

    private function lastCharge(): StripeCharge
    {
        return $this->stripeClient->charges->all(['limit' => 1])['data'][0];
    }

    private function newChargesSince(?StripeCharge $charge): Collection
    {
        $newCharges = $this->stripeClient->charges->all(
            [
                'ending_before' => $charge->id ?? null,
            ]
        )['data'];

        return new Collection($newCharges);
    }
}
