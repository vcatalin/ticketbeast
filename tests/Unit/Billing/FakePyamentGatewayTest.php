<?php

declare(strict_types = 1);

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use Tests\TestCase;

class FakePyamentGatewayTest extends TestCase
{
    /** @test */
    public function charge_with_a_valid_payment_token_is_successfull(): void
    {
        $paymentGateway = new FakePaymentGateway();

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }
}
