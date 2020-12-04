<?php

declare(strict_types=1);

namespace Tests\Unit\Mail;

use App\Mail\OrderConfirmationEmail;
use App\Models\Order;
use Tests\TestCase;

class OrderConfirmationEmailTest extends TestCase
{
    /** @test */
    public function email_contains_a_link_to_the_order_confirmation_page(): void
    {
        $confirmationNumber = 'ORDERCONFIRMATION1234';
        $order = Order::factory()->make([
            'confirmation_number' => $confirmationNumber,
        ]);

        $email = new OrderConfirmationEmail($order);

        $this->assertStringContainsString(url("/orders/{$confirmationNumber}"), $email->render());
    }

    /** @test */
    public function email_has_a_subject(): void
    {
        $order = Order::factory()->make();
        $email = new OrderConfirmationEmail($order);

        $this->assertEquals("Your TicketBeast Order", $email->build()->subject);
    }
}
