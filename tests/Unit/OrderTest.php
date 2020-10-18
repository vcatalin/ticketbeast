<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
     use DatabaseMigrations;

     private const CUSTOMER_EMAIL = 'john@example.com';

     /** @test */
    public function creating_an_order_from_tickets_email_and_amount(): void
    {
        $ticketPrice = 1200;
        $ticketQuantity = 3;

        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create()->addTickets(5);

        $this->assertEquals(5, $concert->ticketsRemaining());

        $amount = $ticketQuantity * $ticketPrice;

        /** @var Order $order */
        $order = Order::forTickets($concert->findTickets($ticketQuantity), 'john@example.com', $amount);

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals($ticketQuantity, $order->ticketQuantity());
        $this->assertEquals($amount, $order->amount);
        $this->assertEquals(2, $concert->ticketsRemaining());
    }

     /** @test */
    public function converting_to_an_array(): void
    {
        $ticketPrice = 1200;
        $ticketQuantity = 3;
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create(['ticket_price' => $ticketPrice])->addTickets($ticketQuantity);

        $order = $concert->orderTickets(self::CUSTOMER_EMAIL, $ticketQuantity);

        $result = $order->toArray();

        $this->assertEquals([
            'email' => self::CUSTOMER_EMAIL,
            'ticket_quantity' => 3,
            'amount' => $ticketPrice * $ticketQuantity,
        ], $result);
    }
}
