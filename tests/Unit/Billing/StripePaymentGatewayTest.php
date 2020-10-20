<?php

declare(strict_types=1);

namespace Tests\Unit\Billing;

use App\Billing\StripePaymentGateway;
use Stripe\Charge;
use Stripe\StripeClient;
use Stripe\StripeClientInterface;
use Tests\TestCase;

class StripePaymentGatewayTest extends TestCase
{
    private StripeClientInterface $stripeClient;
    private Charge $lastCharge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stripeClient = new StripeClient(config('services.stripe.secret'));
        $this->lastCharge = $this->lastCharge();
    }

    /** @test */
    public function charges_with_a_valid_payment_gateway_are_successful(): void
    {
        $paymentGateway = new StripePaymentGateway($this->stripeClient);
        $paymentGateway->charge(1515, $this->getValidTokenId());

        $this->assertCount(1, $this->newCharges());
        $this->assertEquals(1515, $this->lastCharge->amount);
    }

    private function lastCharge(): Charge
    {
        return $this->stripeClient->charges->all(['limit' => 1])['data'][0];
    }

    private function newCharges(): array
    {
        return $this->stripeClient->charges->all(
            [
                'limit' => 1,
                'ending_before' => $this->lastCharge->id,
            ],
            ['api_key' => config('services.stripe.secret')]
        )['data'];
    }

    private function getValidTokenId(): string
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
}
