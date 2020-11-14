<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Billing\Charge;
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
    public function creating_an_order_from_tickets_email_and_charge(): void
    {
        $tickets = Ticket::factory()->count(5)->create();

        $charge = new Charge([
           'amount' => 3600,
           'card_last_four' => '1234'
        ]);

        /** @var Order $order */
        $order = Order::forTickets($tickets, 'john@example.com', $charge);

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(5, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);
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
