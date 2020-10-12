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
    public function converting_to_an_array(): void
    {
        $ticketPrice = 1200;
        $ticketQuantity = 3;
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create(['ticket_price' => $ticketPrice])->addTickets(3);

        $order = $concert->orderTickets(self::CUSTOMER_EMAIL, $ticketQuantity);

        $result = $order->toArray();

        $this->assertEquals([
            'email' => self::CUSTOMER_EMAIL,
            'ticket_quantity' => 3,
            'amount' => $ticketPrice * $ticketQuantity,
        ], $result);
    }

    /** @test */
    public function tickets_are_released_when_an_order_is_cancelled(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create()->addTickets(3);

        $order = $concert->orderTickets(self::CUSTOMER_EMAIL, 3);
        $this->assertEquals(0, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(3, $concert->ticketsRemaining());

        /** @var Order $nullOrder */
        $nullOrder = Order::find($order->id);

        $this->assertNull($nullOrder);
    }
}
