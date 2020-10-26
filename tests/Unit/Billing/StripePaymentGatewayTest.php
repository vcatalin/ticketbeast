<?php

declare(strict_types=1);

namespace Tests\Unit\Billing;

use App\Billing\Exceptions\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Billing\StripePaymentGateway;
use Stripe\Charge;
use Stripe\StripeClient;
use Stripe\StripeClientInterface;
use Tests\TestCase;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    private StripeClientInterface $stripeClient;
    private Charge $lastCharge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stripeClient = new StripeClient(config('services.stripe.secret'));
//        $this->lastCharge = $this->lastCharge();
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fails(): void
    {
        try {
            $paymentGateway = new StripePaymentGateway($this->stripeClient);
            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges());
            return;
        }

        $this->fail('Charging with an invalid payment token did not throw a PaymentFailedException. ');
    }

    protected function getPaymentGateway(): PaymentGateway
    {
        return new StripePaymentGateway($this->stripeClient);
    }

    /** @test */
    public function charges_with_a_valid_payment_gateway_are_successful(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function () use ($paymentGateway) {
            $paymentGateway->charge(1515, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(1515, $newCharges->sum());
    }

    private function newCharges(): array
    {
        return $this->stripeClient->charges->all(
            [
                'limit' => 1,
                'ending_before' => $this->lastCharge->id,
            ]
        )['data'];
    }
}
