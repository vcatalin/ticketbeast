<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        /** @var Order $order */
        $order = Order::factory()->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'amount' => 6000,
            'email' => self::CUSTOMER_EMAIL,
        ]);

        $order->tickets()->saveMany(Ticket::factory()->count(3)->create());

        $this->assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => self::CUSTOMER_EMAIL,
            'ticket_quantity' => 3,
            'amount' => 6000,
        ], $order->toArray());
    }

    /** @test */
    public function can_find_order_by_confirmation_number(): void
    {
        $confirmationNumber = 'FOOBAR123';
        $order = Order::factory()->create([
            'confirmation_number' => $confirmationNumber,
        ]);

        $retrievedOrder = Order::findByConfirmationNumber($confirmationNumber);

        $this->assertEquals($order->id, $retrievedOrder->id);
    }

    /** @test */
    public function exception_is_thrown_when_order_not_found_by_confirmation_number(): void
    {
        try {
            Order::findByConfirmationNumber('RANDOMSTRING');
        } catch (ModelNotFoundException $e) {
            return;
        }

        $this->fail('Order was not found using the given confirmation number and exception was not being thrown.');
    }
}
