<?php

declare(strict_types=1);

namespace Tests\Unit\Billing;

use App\Billing\Exceptions\PaymentFailedException;
use App\Billing\PaymentGateway;

trait PaymentGatewayContractTest
{
    abstract protected function getPaymentGateway(): PaymentGateway;

    /** @test */
    public function charge_with_a_valid_payment_token_is_successful(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function () use ($paymentGateway) {
            $paymentGateway->charge(1515, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(1515, $newCharges->sum());
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fails(): void
    {
        $paymentGateway = $this->getPaymentGateway();
        $newCharges = $paymentGateway->newChargesDuring(function () use ($paymentGateway) {
            try {
                $paymentGateway->charge(1515, 'invalid-payment-token');
            } catch (PaymentFailedException $e) {
                return;
            }
            $this->fail('Charging with an invalid payment token did not throw a PaymentFailedException. ');
        });
        $this->assertCount(0, $newCharges);
    }

    /** @test */
    public function can_fetch_charges_during_a_callback(): void
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(1200, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function () use ($paymentGateway) {
            $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([5000, 3000], $newCharges->all());
    }
}
