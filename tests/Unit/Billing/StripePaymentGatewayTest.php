<?php

declare(strict_types=1);

namespace Tests\Unit\Billing;

use App\Billing\PaymentGateway;
use App\Billing\StripePaymentGateway;
use Stripe\StripeClient;
use Stripe\StripeClientInterface;
use Tests\TestCase;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTest;

    private StripeClientInterface $stripeClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stripeClient = new StripeClient(config('services.stripe.secret'));
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
        $this->assertEquals(1515, $newCharges->map->amount()->sum());
    }
}
