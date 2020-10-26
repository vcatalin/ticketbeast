<?php

declare(strict_types = 1);

namespace Tests\Unit\Billing;

use App\Billing\Exceptions\PaymentFailedException;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    protected function getPaymentGateway(): PaymentGateway
    {
        return new FakePaymentGateway();
    }

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
        $this->assertEquals([3000, 5000], $newCharges->all());
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fails(): void
    {
        try {
            $paymentGateway = new FakePaymentGateway();
            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertEquals(0, $paymentGateway->totalCharges());
            return;
        }

        $this->fail();
    }

    /** @test */
    public function running_a_hook_before_the_first_charge(): void
    {
        $paymentGateway = new FakePaymentGateway();
        $callbackRan = false;
        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$callbackRan){
            $callbackRan = true;
            $this->assertEquals(0, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertTrue($callbackRan);
        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }
}
