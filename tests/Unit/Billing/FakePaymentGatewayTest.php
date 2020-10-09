<?php

declare(strict_types = 1);

namespace Tests\Unit\Billing;

use App\Billing\Exceptions\PaymentFailedException;
use App\Billing\FakePaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charge_with_a_valid_payment_token_is_successfull(): void
    {
        $paymentGateway = new FakePaymentGateway();

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        self::assertEquals(2500, $paymentGateway->totalCharges());
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

        self::fail();
    }
}
